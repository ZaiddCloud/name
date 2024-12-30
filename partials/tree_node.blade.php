@php
    $isExpandable = !empty($node['children']) && $node['bookable_type'] !== 'فقرة';
    $typeClass = match($node['bookable_type']) {
        'كتاب فرعي' => 'sub-book',
        'باب' => 'bab',
        'مسألة' => 'issue',
        'فقرة' => 'paragraph',
        default => 'default',
    };
@endphp

<div class="ml-6 border-l-2 border-gray-200 pl-4 mt-4 {{ $typeClass }}" data-id="{{ $node['id'] }}">
    <div class="flex justify-between items-center">
        <div class="flex-1">
            @if($isExpandable)
                <details class="cursor-pointer">
                    <summary class="font-semibold text-xl">
                        {{ $node['title'] }}
                    </summary>
                    <div class="mt-2 pl-6 sortable" id="sortable-{{ $node['id'] }}">
                        @foreach($node['children'] as $child)
                            @include('books.partials.tree_node', ['node' => $child])
                        @endforeach
                    </div>
                </details>
            @else
                <div class="flex items-center">
                    <p class="font-semibold text-lg">
                        {{ $node['content'] ?? $node['title'] }}
                    </p>
                </div>
            @endif
        </div>
        <div class="flex items-center">
            <svg class="w-4 h-4 ml-2 cursor-move handle" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 10h16M4 14h16" />
            </svg>
        </div>
    </div>
</div>
