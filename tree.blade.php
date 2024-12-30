@if(isset($children) && !empty($children))
    <ul>
        @foreach($children as $child)
            <li>
                {{ $child['title'] }}
                @if(!empty($child['bookable']))
                    - ({{ $child['bookable'] }})
                @endif
                @if(!empty($child['children']))
                    @include('books.tree', ['children' => $child['children']])
                @endif
            </li>
        @endforeach
    </ul>
@endif
