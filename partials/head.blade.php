{{-- books/partials/tree_item.blade.php --}}
<li>
    <div>
        <strong>{{ $head['bookable_type'] }}:</strong> {{ $head['title'] ?? 'عنوان غير متوفر' }}
    </div>

    @if(!empty($head['children']))
        <ul>
            @foreach ($head['children'] as $child)
                @include('books.partials.head', ['head' => $child])
            @endforeach
        </ul>
    @endif
</li>



{{--<ul>--}}
{{--    @foreach($tree as $node)--}}
{{--        <li>--}}
{{--            {{ $node['bookable_type'] }}  <!-- عرض خاصية الرأس -->--}}
{{--            @if(!empty($node['children']))--}}
{{--                <ul>--}}
{{--                    @foreach($node['children'] as $child)--}}
{{--                        <li>--}}
{{--                            {{ $child['bookable_type'] }}  <!-- عرض خاصية الطفل -->--}}
{{--                        </li>--}}
{{--                    @endforeach--}}
{{--                </ul>--}}
{{--            @endif--}}
{{--        </li>--}}
{{--    @endforeach--}}
{{--</ul>--}}
