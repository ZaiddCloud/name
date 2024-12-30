<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class BookService
{
    public function getAllBooks()
    {
        $cachedBooks = Cache::get('all_books');
        $books = $cachedBooks ? unserialize($cachedBooks) : Book::select('id', 'title', 'science_id')->with('heads:id,book_id,parent_id,order,bookable_type')->get();
        return $books;
    }

    public function createBook(array $data)
    {
        try {
            return Book::create($data);
        } catch (Exception $e) {
            Log::error('خطأ في إنشاء الكتاب: ' . $e->getMessage());
            throw new \RuntimeException('غير قادر على إنشاء الكتاب');
        }
    }

    public function getBookWithRelations(Book $book): Book
    {
        $cachedRelations = Cache::get("book_with_relations_{$book->id}");

        if ($cachedRelations) {
            $relations = unserialize($cachedRelations);
            $book->setRelations(['heads' => collect($relations)]);
            return $book;
        } else {
            $relations = $book->load('heads:id,book_id,parent_id,order,bookable_type');
            Cache::put("book_with_relations_{$book->id}", serialize($relations), 60 * 60);
            return $book;
        }
    }

    public function updateBook(Book $book, array $data): bool
    {
        try {
            Cache::forget("book_with_relations_{$book->id}");
            Cache::forget("book_details_{$book->id}");
            return $book->update($data);
        } catch (ModelNotFoundException $e) {
            Log::error('الكتاب غير موجود: ' . $e->getMessage());
            throw new \RuntimeException('الكتاب غير موجود');
        } catch (Exception $e) {
            Log::error('خطأ في تحديث الكتاب: ' . $e->getMessage());
            throw new \RuntimeException('غير قادر على تحديث الكتاب');
        }
    }

    public function deleteBook(Book $book)
    {
        try {
            Cache::forget("book_with_relations_{$book->id}");
            Cache::forget("book_details_{$book->id}");
            return $book->delete();
        } catch (ModelNotFoundException $e) {
            Log::error('الكتاب غير موجود: ' . $e->getMessage());
            throw new \RuntimeException('الكتاب غير موجود');
        } catch (Exception $e) {
            Log::error('خطأ في حذف الكتاب: ' . $e->getMessage());
            throw new \RuntimeException('غير قادر على حذف الكتاب');
        }
    }

    public function getBookDetailsWithTree(Book $book): array
    {
        $cachedData = Cache::get("book_details_{$book->id}");

        if ($cachedData) {
            $data = is_string($cachedData) ? unserialize($cachedData) : $cachedData;

            // تتبع البيانات المخزنة في الكاش
            Log::info('Cached data:', $data);

            // تأكد من أن 'book' هو كائن Eloquent
            if (is_array($data['book'])) {
                $data['book'] = Book::find($data['book']['id']);
            }

            // تتبع البيانات بعد فك الضغط
            Log::info('Unserialized data:', $data);

            return $data;
        } else {
            $heads = $this->fetchHeads($book);
            $tree = $this->buildTree($heads);
            $data = [
                'book' => $book,
                'tree' => $tree,
            ];


            // تتبع البيانات قبل تخزينها في الكاش
            Log::info('Data to be cached:', $data);

            Cache::put("book_details_{$book->id}", serialize($data), 60 * 60);
            return $data;
        }
    }

    private function fetchHeads(Book $book)
    {
        // استخدام التحديد لجلب الأعمدة المطلوبة فقط
        $heads = $book->heads()->with('bookable')->select('id', 'parent_id', 'bookable_type', 'order', 'bookable_id')->orderBy('parent_id')->orderBy('order')->get();
        return $heads->groupBy('parent_id');
    }

    private function buildTree($headsByParent)
    {
        $tree = [];
        $stack = [null];

        while ($stack) {
            $parentId = array_pop($stack);
            if (isset($headsByParent[$parentId])) {
                foreach ($headsByParent[$parentId] as $head) {
                    $node = $this->createNode($head);
                    $tree[] = $node;
                    $stack[] = $head->id;
                }
            }
        }

        usort($tree, function ($a, $b) {
            return $a['order'] - $b['order'];
        });

        return $this->assembleFinalTree($tree);
    }

    private function createNode($head)
    {

        // تتبع البيانات
        Log::info('Head data:', $head->toArray());

        $node = [
            'id' => $head->id,
            'parent_id' => $head->parent_id,
            'bookable_type' => $head->bookable_type,
            'children' => [],
            'order' => $head->order,
        ];

        if ($head->bookable_type == 'فقرة') {
            $node['content'] = $head->bookable->content ?? __('Content Not Available');
        } else {
            $node['title'] = $head->bookable->title ?? __('Title Not Available');
        }

        // تتبع بيانات العقدة
        Log::info('Node data:', $node);

        return $node;
    }

    private function assembleFinalTree($tree)
    {
        $treeById = [];
        foreach ($tree as $node) {
            $treeById[$node['id']] = $node;
        }

        foreach ($tree as $node) {
            if ($node['parent_id'] !== null) {
                $treeById[$node['parent_id']]['children'][] = &$treeById[$node['id']];
            }
        }

        return array_values(array_filter($treeById, function ($node) {
            return is_null($node['parent_id']);
        }));
    }




//    public function getBookDetailsWithTree(Book $book): array
//    {
//        Log::info('بدء جلب تفاصيل الكتاب وبناء الشجرة للكتاب ID: ' . $book->id);
//
//        return Cache::remember("book_details_{$book->id}", 60 * 60, function () use ($book) {
//            // جلب الرؤوس مع أنواع الكتاب القابلة فقط في استعلام واحد
//            $heads = $book->heads()->with('bookable')->orderBy('bookable_type')->get();
//            $headsByParent = $heads->groupBy('parent_id');
//            Log::info('تم تحميل الرؤوس: ' . $heads->count());
//
//            $tree = [];
//            $stack = [null];
//
//            // بناء الشجرة باستخدام الرؤوس التي تم جلبها
//            while ($stack) {
//                $parentId = array_pop($stack);
//                if (isset($headsByParent[$parentId])) {
//                    foreach ($headsByParent[$parentId] as $head) {
//                        $node = [
//                            'id' => $head->id,
//                            'parent_id' => $head->parent_id,
//                            'bookable_type' => $head->bookable_type,
//                            'children' => [],
//                        ];
//
//                        if ($head->bookable_type == 'فقرة') {
//                            $node['content'] = $head->bookable->content ?? __('Content Not Available');
//                        } else {
//                            $node['title'] = $head->bookable->title ?? __('Title Not Available');
//                        }
//
//                        $tree[] = $node;
//                        $stack[] = $head->id;
//                    }
//                }
//            }
//
//            $treeById = [];
//            foreach ($tree as $node) {
//                $treeById[$node['id']] = $node;
//            }
//
//            foreach ($tree as $node) {
//                if ($node['parent_id'] !== null) {
//                    $treeById[$node['parent_id']]['children'][] = &$treeById[$node['id']];
//                }
//            }
//
//            $finalTree = array_values(array_filter($treeById, function ($node) {
//                return is_null($node['parent_id']);
//            }));
//
//            Log::info('تم بناء الشجرة: ', $finalTree);
//
//            return [
//                'book' => $book,
//                'tree' => $finalTree,
//            ];
//        });
//    }


//    /**
//     * بناء هيكل شجرة لرؤوس الكتب.
//     *
//     * @param Book $book
//     * @return array
//     */
//    public function buildBookHeadTree(Book $book): array
//    {
//        Log::info('بدء بناء الشجرة للكتاب ID: ' . $book->id);
//
//        return Cache::remember("book_tree_{$book->id}", 60 * 60, function () use ($book) {
//            $heads = $book->heads()->with('bookable')->get();
//            $headsByParent = $heads->groupBy('parent_id');
//            Log::info('تم تحميل الرؤوس: ' . $heads->count());
//
//            $tree = [];
//            $stack = [null];
//
//            while ($stack) {
//                $parentId = array_pop($stack);
//                if (isset($headsByParent[$parentId])) {
//                    foreach ($headsByParent[$parentId] as $head) {
//                        $node = [
//                            'id' => $head->id,
//                            'parent_id' => $head->parent_id,
//                            'bookable_type' => $head->bookable_type,
//                            'children' => [],
//                        ];
//
//                        if ($head->bookable_type == 'فقرة') {
//                            $node['content'] = $head->bookable->content ?? 'محتوى غير متوفر';
//                        } else {
//                            $node['title'] = $head->bookable->title ?? 'عنوان غير متوفر';
//                        }
//
//                        $tree[] = $node;
//                        $stack[] = $head->id;
//                    }
//                }
//            }
//
//            $treeById = [];
//            foreach ($tree as $node) {
//                $treeById[$node['id']] = $node;
//            }
//
//            foreach ($tree as $node) {
//                if ($node['parent_id'] !== null) {
//                    $treeById[$node['parent_id']]['children'][] = &$treeById[$node['id']];
//                }
//            }
//
//            $finalTree = array_values(array_filter($treeById, function ($node) {
//                return is_null($node['parent_id']);
//            }));
//
//            Log::info('تم بناء الشجرة: ', $finalTree);
//            return $finalTree;
//        });
//    }
//
//
//
//    /**
//     * احصل على جميع رؤوس الكتب.
//     *
//     * @return \Illuminate\Support\Collection
//     */
//    public function getBookHeads()
//    {
//        return Cache::remember('book_heads', 60 * 60, function () {
//            $books = Book::with('heads.bookable')->get();
//
//            $books->load(['heads' => function ($query) {
//                $query->whereIn('bookable_type', ['كتاب فرعي', 'باب', 'مسألة', 'فقرة'])
//                    ->orderByRaw("FIELD(bookable_type, 'كتاب فرعي', 'باب', 'مسألة', 'فقرة')");
//            }]);
//
//            $allHeads = $books->flatMap(function ($book) {
//                return $book->heads->map(function ($head) {
//                    if ($head->bookable_type == 'فقرة') {
//                        return !empty($head->bookable->content) ? $head->bookable->content : 'محتوى غير متوفر';
//                    } else {
//                        return !empty($head->bookable->title) ? $head->bookable->title : 'عنوان غير متوفر';
//                    }
//                });
//            });
//
//            return $allHeads;
//        });
//    }
}

