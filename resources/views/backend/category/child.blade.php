<ul>
    @foreach ($childs as $child)
        <li class="jstree-open"
            data-jstree="{&quot;selected&quot;:@if (isset($cat) && $cat->id == $child->id) true @else false @endif,&quot;type&quot;:&quot;file&quot;}">
            <div class="jstree-anchor" data-bs-toggle="tooltip" data-bs-title="{{ $category->title }}">
                <span>{{ $child->title }}</span>
                @can('backend.service_category.edit', $child)
                    <div class="actions">
                        <a id="edit-category" href="#">
                            <img class="edit-child" data-url={{ route('backend.category.edit', ['category' => $child->id, 'locale' =>  request()->locale ?? app()->getlocale()]) }} value="{{ $child->id }}" src="{{ asset('admin/images/svg/edit-2.svg') }}">
                        </a>
                        <a href="#confirmationModal{{$child->id}}" data-bs-toggle="modal">
                            <img class="remove-icon" src="{{ asset('admin/images/svg/trash-table.svg') }}">
                        </a>
                    </div>
                @endcan
            </div>
            @if (count($child->childs))
                @include('backend.category.child', ['childs' => $child->childs])
            @endif
        </li>
    @endforeach
</ul>
