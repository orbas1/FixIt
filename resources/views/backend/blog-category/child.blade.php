<ul>
    @foreach ($childs as $child)
        <li class="jstree-open"
            data-jstree="{&quot;selected&quot;:@if (isset($cat) && $cat->id == $child->id) true @else false @endif,&quot;type&quot;:&quot;file&quot;}">
            <div class="jstree-anchor" data-bs-toggle="tooltip" data-bs-title="{{ $category->title }}">
                <span>{{ $child->title }}</span>
                @canAny(['backend.blog_category.edit', 'backend.blog_category.destroy'])
                    <div class="actions">
                        @can('backend.blog_category.edit')
                            <a id="edit-category" href="#">
                                <img class="edit-child" {{ route('backend.blog-category.edit', ['blog_category' => $child->id, 'locale' =>  request()->locale ?? app()->getlocale()]) }} value="{{ $child->id }}" src="{{ asset('admin/images/svg/edit-2.svg') }}">
                            </a>
                        @endcan
                        @can('backend.blog_category.destroy')
                            <a href="#confirmationModal{{$child->id}}" data-bs-toggle="modal">
                                <img class="remove-icon" src="{{ asset('admin/images/svg/trash-table.svg') }}">
                            </a>
                        @endcan
                    </div>
                @endcanAny
            </div>
            @if (count($child->childs))
                @include('backend.blog-category.child', ['childs' => $child->childs])
            @endif
        </li>
    @endforeach
</ul>
