import './bootstrap';
import Sortable from 'sortablejs';

document.addEventListener('DOMContentLoaded', function () {
    const initSortable = (container) => {
        try {
            if (!container) {
                console.error('Container not found for sortable initialization.');
                return;
            }
            console.log('Initializing sortable for container:', container);

            return new Sortable(container, {
                animation: 150,
                handle: '.handle', // استخدام handle كزر للسحب
                ghostClass: 'sortable-ghost',
                onEnd: function (/**Event*/evt) {
                    let order = [];
                    const items = container.querySelectorAll('[data-id]');

                    console.log('Sortable items:', items);

                    if (items.length === 0) {
                        console.error('No sortable items found.');
                        return;
                    }

                    items.forEach((item, index) => {
                        order.push({ id: item.getAttribute('data-id'), order: index + 1 });
                    });

                    console.log('Order to be sent:', { orders: order });

                    if (order.length === 0) {
                        console.error('No items to reorder.');
                        return;
                    }

                    fetch('/update-order', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ orders: order })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            console.log('Order updated successfully');
                            initAllSortables(); // إعادة تهيئة SortableJS بعد التحديث الناجح
                        } else {
                            console.error('Error updating order:', data.message);
                        }
                    });
                }
            });
        } catch (error) {
            console.error('Error initializing sortable:', error);
        }
    };

    const initAllSortables = () => {
        try {
            const sortableContainers = document.querySelectorAll('[id^=sortable-]');
            if (sortableContainers.length === 0) {
                console.error('No sortable containers found.');
                return;
            }
            sortableContainers.forEach(container => {
                console.log('Found sortable container:', container);
                initSortable(container);
            });
        } catch (error) {
            console.error('Error initializing all sortables:', error);
        }
    };

    window.addEventListener('load', initAllSortables); // تهيئة جميع العناصر القابلة للسحب عند تحميل الصفحة بالكامل
});

