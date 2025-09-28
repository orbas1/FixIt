<div class="left-jstree-box">
    <div class="row g-sm-4 g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5>{{ __('static.categories.categories') }}</h5>
                    @isset($cat)
                        <div class="btn-popup mb-0">
                            <a href="{{ route('backend.category.index') }}" class="btn btn-primary btn-sm">
                                <i data-feather="plus"></i>{{ __('static.categories.category') }}
                            </a>
                        </div>
                    @endisset
                </div>
                <div class="card-body position-relative no-data">
                    <form class="d-flex gap-2" action="" method="get">
                        <input type="text" name="search" id="searchCategory" value="{{ request()->search }}"
                            class="form-control" placeholder="Search Category...">
                        <button id="submitBtn" type="submit" class="btn btn-primary"> {{ __('Search') }}</button>
                    </form>
                    <div class="jstree-main-box">
                        <div class="jstree-loader">
                            <img src="{{ asset('admin/images/loader.gif') }}" class="img-fluid">
                        </div>
                        <div id="treeBasic" style="display: none">
                            <ul>
                                @forelse($categories as $category)
                                    <li class="jstree-open"
                                        data-jstree='{&quot;selected&quot;:@if (isset($cat) && $cat->id == $category->id) true @else false @endif,"icon":"{{ asset('admin/images/menu.png') }}"}'>

                                        <div class="jstree-anchor" data-bs-toggle="tooltip"
                                            data-bs-title="{{ $category->title }}">
                                            <span>
                                                {{ $category->title }}
                                                ({{ count($category->childs) }})
                                            </span>
                                            @can('backend.service_category.edit', $category)
                                                <div class="actions">
                                                    <a id="edit-category" href="#">
                                                        <img class="edit-icon"
                                                            data-url="{{ route('backend.category.edit', ['category' => $category->id, 'locale' => request()->locale ?? app()->getlocale()]) }}"
                                                            value="{{ $category->id }}"
                                                            src="{{ asset('admin/images/svg/edit-2.svg') }}">
                                                    </a>
                                                    <a href="#confirmationModal{{ $category->id }}" data-bs-toggle="modal">
                                                        <img class="remove-icon"
                                                            src="{{ asset('admin/images/svg/trash-table.svg') }}">
                                                    </a>
                                                </div>
                                            @endcan
                                        </div>
                                        @if (count($category->childs))
                                            @include('backend.category.child', [
                                                'childs' => $category->childs,
                                                'cat' => $cat,
                                            ])
                                        @endif
                                    </li>
                                @empty
                                    <li class="d-flex flex-column no-data-detail">
                                        <img class="mx-auto d-flex" src="{{ asset('admin/images/no-category.png') }}"
                                            alt="">
                                        <div class="data-not-found">
                                            <span>{{ __('static.categories.no_category') }}</span>
                                        </div>
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@isset($categories)
    @foreach ($categories as $category)
        <div class="modal fade" id="confirmationModal{{ $category->id }}" tabindex="-1"
            aria-labelledby="confirmationModalLabel{{ $category->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-start">
                        <div class="main-img">
                            <img src="{{ asset('admin/images/svg/trash-dark.svg') }}" alt="">
                        </div>
                        <div class="text-center">
                            <div class="modal-title"> {{ __('static.delete_message') }}</div>
                            <p>{{ __('static.delete_note') }}</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <form action="{{ route('backend.category.destroy', $category->id) }}" method="post">
                            @csrf
                            @method('delete')
                            <button class="btn cancel" data-bs-dismiss="modal"
                                type="button">{{ __('static.cancel') }}</button>
                            <button class="btn btn-primary delete" type="submit">{{ __('static.delete') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endisset

@isset($childs)
    @foreach ($childs as $child)
        <div class="modal fade" id="confirmationModal{{ $child->id }}" tabindex="-1"
            aria-labelledby="confirmationModalLabel{{ $child->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-start">
                        <div class="main-img">
                            <img src="{{ asset('admin/images/svg/trash-dark.svg') }}" alt="">
                        </div>
                        <div class="text-center">
                            <div class="modal-title"> {{ __('static.delete_message') }}</div>
                            <p>{{ __('static.delete_note') }}</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <form action="{{ route('backend.category.destroy', $child->id) }}" method="post">
                            @csrf
                            @method('delete')
                            <button class="btn cancel" data-bs-dismiss="modal"
                                type="button">{{ __('static.cancel') }}</button>
                            <button class="btn btn-primary delete" type="submit">{{ __('static.delete') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endisset
