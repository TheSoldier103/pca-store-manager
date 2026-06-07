jQuery(document).ready(function ($) {

    /* =============================================================
       SALES
    ============================================================= */

    $(document).on('change', '.pca-fulfill-owed', function () {
        let id = $(this).data('id');

        if (!confirm("Mark this owed item as fulfilled?")) {
            $(this).prop('checked', false);
            return;
        }

        $.post(ajaxurl, {
            action: 'pca_store_fulfill_single_owed_item',
            id: id
        }, function (response) {
            alert(response.data.message);
            if (response.success) location.reload();
        });
    });


    function loadItemStock(item_id, campus_id) {
        return $.post(ajaxurl, {
            action: 'pca_store_get_item_stock',
            item_id: item_id,
            campus_id: campus_id
        });
    }


    let department = $('#pca-sale-department').val();

    if (department === 'books' && $('#pca-sale-item option:first').text() === 'Select Pack') {

        $(document).on('change', '#pca-sale-campus', loadPackItems);

        if ($('#pca-sale-campus').val()) {
            loadPackItems();
        }
    }

    if (department === 'stationery') {

        $(document).on('change', '#pca-sale-campus', loadStationeryItems);

        if ($('#pca-sale-campus').val()) {
            loadStationeryItems();
        }
    }


    function loadPackItems() {

        let campus_id = $('#pca-sale-campus').val();

        $.post(ajaxurl, {
            action: 'pca_store_get_book_packs',
            campus_id: campus_id
        }, function(response) {

            let dropdown = $('#pca-sale-item');
            dropdown.empty().append('<option value="">Select Pack</option>');

            if (!response.success || !response.data.items) return;

            response.data.items.forEach(function(item) {
                dropdown.append(
                    `<option value="${item.id}" data-price="${item.selling_price}">
                        ${item.name}
                    </option>`
                );
            });
        });
    }

    // Load stationery items
    function loadStationeryItems() {

        let campus_id = $('#pca-sale-campus').val();

        $.post(ajaxurl, {
            action: 'pca_store_get_stationery_items',
            campus_id: campus_id
        }, function(response) {

            let dropdown = $('#pca-sale-item');
            dropdown.empty().append('<option value="">Select Item</option>');

            if (!response.success || !response.data.items) return;

            response.data.items.forEach(function(item) {
                dropdown.append(
                    `<option value="${item.id}" data-price="${item.selling_price}">
                        ${item.name}
                    </option>`
                );
            });
        });
    }


    function updateSaleTotal() {
        let price = parseFloat($('#pca-sale-price').val()) || 0;
        let qty = parseInt($('#pca-sale-qty').val()) || 1;
        let discount = parseFloat($('#pca-sale-discount').val()) || 0;

        let total = (price * qty) - discount;
        $('#pca-sale-total').val(total);
    }

    // When item changes → update price → then update total
    $('#pca-sale-item').on('change', function(){
        let price = $('option:selected', this).data('price') || 0;
        $('#pca-sale-price').val(price);
        updateSaleTotal();
    });

    // Trigger recalculation
    $('#pca-sale-qty').on('input', updateSaleTotal);
    $('#pca-sale-discount').on('input', updateSaleTotal);


    // Auto-fill price when selecting item (single handler — removed duplicate)
    $(document).on('change', '#pca-sale-item', function () {
        let price = $(this).find(':selected').data('price') || 0;
        $('#pca-sale-price').val(price);
    });

    // Filtered item dropdowns (sale form)
    function loadFilteredSaleItems() {
        $.post(ajaxurl, {
            action:      'pca_store_get_filtered_items',
            class_level: $('#pca-sale-class').val(),
            subject:     $('#pca-sale-subject').val(),
            department:  $('#pca-sale-department').val(),   // NEW
            campus_id:   $('#pca-sale-campus').val()        // NEW
        }, function (response) {
            if (!response.success) return;

            let dropdown = $('#pca-sale-item');
            dropdown.empty().append('<option value="">Select Book</option>');

            response.data.items.forEach(function (item) {
                dropdown.append(
                    `<option value="${item.id}" data-price="${item.selling_price}">
                        ${item.name}
                    </option>`
                );
            });
        });
    }


    $(document).on('change', '#pca-sale-class, #pca-sale-subject', loadFilteredSaleItems);


    // Record sale with owed items
    $('#pca-record-sale-btn').on('click', function (e) {
        e.preventDefault();

        const item_id   = $('#pca-sale-item').val();
        const qty       = parseInt($('#pca-sale-qty').val());
        const campus_id = $('#pca-sale-campus').val();

        if (!item_id || !qty || !campus_id) {
            alert("Please select item, quantity, and campus.");
            return;
        }

        loadItemStock(item_id, campus_id).done(handleStockResponse);

        function handleStockResponse(response) {
            if (!response.success) {
                alert("Could not load stock.");
                return;
            }

            const available = parseInt(response.data.stock);
            const owed      = qty > available ? (qty - available) : 0;

            if (owed > 0 && !confirmOwedSale(available, owed)) return;

            submitSale(owed);
        }

        function confirmOwedSale(available, owed) {
            if ($('#pca-owed-confirm').length === 0) {
                $('#pca-record-sale-btn').before(`
                    <div id="pca-owed-warning" style="
                        display: flex;
                        align-items: flex-start;
                        gap: 10px;
                        margin: 10px 0;
                        padding: 12px 14px;
                        border-left: 4px solid #cc0000;
                        background: #fff5f5;
                        border-radius: 4px;
                        font-size: 13px;
                        color: #333;
                    ">
                        <span style="font-size:18px; line-height:1;">⚠️</span>
                        <div>
                            <strong>Stock shortage:</strong> Only ${available} available — ${owed} will be owed.<br>
                            <label style="display:inline-flex; align-items:center; gap:6px; margin-top:6px; cursor:pointer;">
                                <input type="checkbox" id="pca-owed-confirm">
                                I confirm this sale should proceed with owed items.
                            </label>
                        </div>
                    </div>
                `);
            }

            if (!$('#pca-owed-confirm').is(':checked')) {
                alert("Please confirm that you want to proceed with owed items.");
                return false;
            }

            return true;
        }

        function submitSale(owed) {
            $.post(ajaxurl, {
                action:         'pca_store_record_sale',
                item_id:        item_id,
                qty:            qty,
                price:          $('#pca-sale-price').val(),
                discount:       $('#pca-sale-discount').val() || 0,
                receipt_no:     $('#pca-sale-receipt').val(),
                payment_method: $('#pca-sale-method').val(),
                notes:          $('#pca-sale-notes').val(),
                department:     $('#pca-sale-department').val(),
                campus_id:      campus_id,
                has_owed_items: owed > 0 ? 1 : 0,
                qty_owed:       owed,
            }, function (response) {
                alert(response.data.message);
                if (response.success) location.reload();
            });
        }
    });



    /* =============================================================
       STOCK (add / damage / correction / return)
    ============================================================= */

    // Filtered item dropdown (stock form)
    function loadFilteredStockItems() {
        $.post(ajaxurl, {
            action:      'pca_store_get_filtered_items',
            class_level: $('#pca-stock-class').val(),
            subject:     $('#pca-stock-subject').val(),
            department: 'books', 
            campus_id: $('#pca-stock-campus').val() 
        }, function (response) {
            if (!response.success) return;

            let dropdown = $('#pca-stock-item');
            dropdown.empty().append('<option value="">Select Item</option>');

            response.data.items.forEach(function (item) {
                dropdown.append(`<option value="${item.id}">${item.name}</option>`);
            });
        });
    }

    $(document).on('change', '#pca-stock-class, #pca-stock-subject', loadFilteredStockItems);

    $('#pca-save-stock').on('click', function () {
        $.post(ajaxurl, {
            action:    'pca_store_add_stock',
            campus_id: $('#pca-stock-campus').val(),
            item_id:   $('#pca-stock-item').val(),
            qty:       $('#pca-stock-qty').val(),
            notes:     $('#pca-stock-notes').val()
        }, function (response) {
            alert(response.data.message);
            location.reload();
        });
    });

    $('#pca-save-damage').on('click', function () {
        $.post(ajaxurl, {
            action:  'pca_store_damage_stock',
            item_id: $('#pca-damage-item').val(),
            qty:     $('#pca-damage-qty').val(),
            reason:  $('#pca-damage-reason').val(),
            notes:   $('#pca-damage-notes').val()
        }, function (response) {
            alert(response.data.message);
            location.reload();
        });
    });

    $('#pca-save-correction').on('click', function () {
        $.post(ajaxurl, {
            action:   'pca_store_correct_stock',
            item_id:  $('#pca-correction-item').val(),
            new_qty:  $('#pca-correction-new').val(),
            notes:    $('#pca-correction-notes').val()
        }, function (response) {
            alert(response.data.message);
            location.reload();
        });
    });

    $('#pca-save-return').on('click', function () {
        $.post(ajaxurl, {
            action:  'pca_store_return_stock',
            item_id: $('#pca-return-item').val(),
            qty:     $('#pca-return-qty').val(),
            reason:  $('#pca-return-reason').val(),
            notes:   $('#pca-return-notes').val()
        }, function (response) {
            alert(response.data.message);
            location.reload();
        });
    });


    /* =============================================================
       ITEMS (single items — books, stationery, uniforms)
    ============================================================= */

    function toggleItemFields() {
        const dept = $('#pca-item-department option:selected').text().trim().toLowerCase();
        $('.pca-book-field').toggle(dept === 'books');
        $('.pca-uniform-field').toggle(dept === 'uniforms');
    }

    function resetItemModal() {
        $('#pca-item-id, #pca-item-name, #pca-item-price, #pca-item-reorder').val('');
        $('#pca-item-class, #pca-item-subject').val('');
        $('#pca-item-size, #pca-item-gender, #pca-item-color').val('');
        $('#pca-item-department, #pca-item-supplier').val('');
        $('#pca-item-type').val('single');
        $('#pca-item-department').trigger('change');
    }

    // Load suppliers when department changes
    $('#pca-item-department').on('change', function () {
        const deptId = $(this).val();
        $('#pca-item-supplier').html('<option value="">Loading...</option>');

        $.get(ajaxurl, {
            action:        'pca_get_suppliers_by_department',
            department_id: deptId
        }, function (response) {
            let html = '<option value="">Select Supplier</option>';
            if (response.success && response.data.suppliers.length > 0) {
                response.data.suppliers.forEach(function (s) {
                    html += `<option value="${s.id}">${s.name}</option>`;
                });
            }
            $('#pca-item-supplier').html(html);
        });

        toggleItemFields();
    });

    // Open item modal helpers
    function openItemModal(deptName, title) {
        resetItemModal();
        $('#pca-item-department option').filter(function () {
            return $(this).text().trim().toLowerCase() === deptName;
        }).prop('selected', true);
        $('#pca-item-department').trigger('change');
        $('#pca-item-modal-title').text(title);
        $('#pca-add-item-modal').show();
    }

    $(document).on('click', '#pca-add-book-btn',       () => openItemModal('books',      'Add New Book'));
    $(document).on('click', '#pca-add-stationery-btn', () => openItemModal('stationery', 'Add New Stationery'));
    $(document).on('click', '#pca-add-uniform-btn',    () => openItemModal('uniforms',   'Add New Uniform Item'));

    // Edit item
    $(document).on('click', '.pca-edit-item', function (e) {
        e.preventDefault();
        const id = $(this).data('id');

        $.get(ajaxurl, { action: 'pca_store_get_item', id }, function (response) {
            if (!response.success) { alert(response.data.message); return; }

            const item = response.data.item;
            resetItemModal();

            $('#pca-item-id').val(item.id);
            $('#pca-item-name').val(item.name);
            $('#pca-item-price').val(item.selling_price);
            $('#pca-item-reorder').val(item.reorder_level);
            $('#pca-item-class').val(item.class_level);
            $('#pca-item-subject').val(item.subject);
            $('#pca-item-size').val(item.size);
            $('#pca-item-gender').val(item.gender);
            $('#pca-item-color').val(item.color);

            $('#pca-item-department').val(item.department_id).trigger('change');
            setTimeout(() => $('#pca-item-supplier').val(item.supplier_id), 300);

            $('#pca-item-modal-title').text('Edit Item');
            $('#pca-add-item-modal').show();
        });
    });

    // Save item
    $('#pca-save-item').on('click', function (e) {
        e.preventDefault();

        $.post(ajaxurl, {
            action:        'pca_store_save_item',
            item_type:     'single',
            id:            $('#pca-item-id').val(),
            name:          $('#pca-item-name').val(),
            department_id: $('#pca-item-department').val(),
            supplier_id:   $('#pca-item-supplier').val(),
            selling_price: $('#pca-item-price').val(),
            reorder_level: $('#pca-item-reorder').val(),
            class_level:   $('#pca-item-class').val(),
            subject:       $('#pca-item-subject').val(),
            size:          $('#pca-item-size').val(),
            gender:        $('#pca-item-gender').val(),
            color:         $('#pca-item-color').val(),
        }, function (response) {
            alert(response.data.message);
            if (response.success) location.reload();
        });
    });

    // Delete item
    $(document).on('click', '.pca-delete-item', function (e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this item?')) return;

        $.post(ajaxurl, {
            action: 'pca_store_delete_item',
            id:     $(this).data('id')
        }, function (response) {
            alert(response.data.message);
            if (response.success) location.reload();
        });
    });

    $('#pca-close-item-modal').on('click', () => $('#pca-add-item-modal').hide());


    /* =============================================================
       PACKS
    ============================================================= */

    let selectedPackItems = {};

    function resetPackModal() {
        selectedPackItems = {};
        $('#pca-pack-id').val('');
        $('#pca-pack-name').val('');
        $('#pca-pack-class').val('');
        $('#pca-pack-price').val('');
        $('#pca-pack-reorder').val('');
        $('#pca-pack-class-filter').val('');
        $('#pca-pack-subject-filter').val('');
        $('#pca-pack-book-list').html('<p>Select a class or subject to view books.</p>');
        $('#pca-pack-save-feedback').text('').hide();
        renderSelectedBooks();
    }

    function renderSelectedBooks() {
        const tbody = $('#pca-selected-books-body');
        const items = Object.values(selectedPackItems);

        tbody.empty();

        if (items.length === 0) {
            tbody.append('<tr><td colspan="3"><em>No books selected yet.</em></td></tr>');
            $('#pca-selected-count').text('(0)');
            return;
        }

        items.forEach(item => {
            tbody.append(`
                <tr data-id="${item.id}">
                    <td>${item.name}</td>
                    <td>
                        <input type="number" class="pca-selected-qty small-text"
                            data-id="${item.id}" value="${item.qty}" min="1">
                    </td>
                    <td>
                        <button class="button button-small pca-remove-selected" data-id="${item.id}">✕</button>
                    </td>
                </tr>
            `);
        });

        $('#pca-selected-count').text(`(${items.length})`);
    }

    function autoCalcPackPrice() {
        let total = 0;
        Object.values(selectedPackItems).forEach(item => {
            total += parseFloat(item.price || 0) * parseInt(item.qty || 1);
        });
        $('#pca-pack-price').val(total);
    }

    function loadPackBooks() {
        const classLevel = $('#pca-pack-class-filter').val();
        const subject    = $('#pca-pack-subject-filter').val();

        if (!classLevel && !subject) {
            $('#pca-pack-book-list').html('<p>Select a class or subject to view books.</p>');
            return;
        }

        $('#pca-pack-book-list').html('<p><em>Loading…</em></p>');

        $.get(ajaxurl, {
            action:   'pca_store_get_books_for_pack',
            class:    classLevel,
            subject:  subject,
        }, function (response) {
            if (!response.success) {
                $('#pca-pack-book-list').html('<p>Failed to load books.</p>');
                return;
            }

            const books = response.data.books;

            if (!books.length) {
                $('#pca-pack-book-list').html('<p>No books found for these filters.</p>');
                return;
            }

            let html = `
                <table class="widefat striped">
                    <thead><tr><th width="30">✓</th><th>Book</th></tr></thead>
                    <tbody>
            `;

            books.forEach(book => {
                const checked = selectedPackItems[book.id] ? 'checked' : '';
                const meta    = [book.class_level, book.subject].filter(Boolean).join(' – ');
                html += `
                    <tr>
                        <td>
                            <input type="checkbox" class="pca-pack-select"
                                data-id="${book.id}"
                                data-name="${book.name}"
                                data-price="${book.selling_price}"
                                ${checked}>
                        </td>
                        <td>${book.name}${meta ? ' <small>(' + meta + ')</small>' : ''}</td>
                    </tr>
                `;
            });

            html += '</tbody></table>';
            $('#pca-pack-book-list').html(html);
        });
    }

    // Checkbox: add / remove book from pack
    $(document).on('change', '.pca-pack-select', function () {
        const id = $(this).data('id');

        if ($(this).is(':checked')) {
            selectedPackItems[id] = {
                id,
                name:  $(this).data('name'),
                qty:   1,
                price: parseFloat($(this).data('price'))
            };
        } else {
            delete selectedPackItems[id];
        }

        renderSelectedBooks();
        autoCalcPackPrice();
    });

    // Remove from selected table (keep checkbox in sync)
    $(document).on('click', '.pca-remove-selected', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        delete selectedPackItems[id];
        $(`.pca-pack-select[data-id="${id}"]`).prop('checked', false);
        renderSelectedBooks();
    });

    // Quantity change
    $(document).on('input', '.pca-selected-qty', function () {
        const id  = $(this).data('id');
        const qty = parseInt($(this).val(), 10);
        if (selectedPackItems[id] && qty > 0) {
            selectedPackItems[id].qty = qty;
            autoCalcPackPrice();
        }
    });

    $(document).on('change', '#pca-pack-class-filter, #pca-pack-subject-filter', loadPackBooks);

    // Open pack modal
    $(document).on('click', '#pca-add-pack-btn', function (e) {
        e.preventDefault();
        resetPackModal();
        $('#pca-pack-modal-title').text('Add New Pack');
        $('#pca-add-pack-modal').show();
    });

    $(document).on('click', '#pca-add-uniform-pack-btn', function (e) {
        e.preventDefault();
        resetPackModal();
        $('#pca-pack-department option').filter(function () {
            return $(this).text().trim().toLowerCase() === 'uniforms';
        }).prop('selected', true);
        $('#pca-pack-department').trigger('change');
        $('#pca-pack-modal-title').text('Add New Uniform Pack');
        $('#pca-add-pack-modal').show();
    });

    // Edit pack
    $(document).on('click', '.pca-edit-pack', function (e) {
        e.preventDefault();
        const packId = $(this).data('id');

        selectedPackItems = {};
        renderSelectedBooks();

        $.get(ajaxurl, { action: 'pca_store_get_pack', pack_id: packId }, function (response) {
            if (!response.success) { alert('Could not load pack'); return; }

            const pack  = response.data.pack;
            const items = response.data.items;

            $('#pca-pack-id').val(pack.id);
            $('#pca-pack-name').val(pack.name);
            $('#pca-pack-class').val(pack.class_level);
            $('#pca-pack-price').val(pack.selling_price);
            $('#pca-pack-reorder').val(pack.reorder_level);

            items.forEach(item => {
                selectedPackItems[item.id] = { id: item.id, name: item.name, qty: item.qty };
            });

            renderSelectedBooks();
            $('#pca-pack-modal-title').text('Edit Pack');
            $('#pca-add-pack-modal').show();
            loadPackBooks();
        });
    });

    // Save pack
    $(document).on('click', '#pca-save-pack', function (e) {
        e.preventDefault();

        const name  = $('#pca-pack-name').val().trim();
        const items = Object.values(selectedPackItems);

        if (!name) { alert('Please enter a pack name.'); $('#pca-pack-name').focus(); return; }
        if (!items.length) { alert('Please select at least one book for this pack.'); return; }

        const $btn = $(this).prop('disabled', true).text('Saving…');

        $.post(ajaxurl, {
            action:        'pca_store_save_item',
            id:            $('#pca-pack-id').val(),
            item_type:     'pack',
            name,
            department_id: $('#pca-pack-department-id').val(),
            supplier_id:   0,
            selling_price: $('#pca-pack-price').val()   || 0,
            reorder_level: $('#pca-pack-reorder').val() || 0,
            class_level:   $('#pca-pack-class').val(),
            pack_items:    items,
        }, function (response) {
            if (response.success) {
                $('#pca-add-pack-modal').hide();
                resetPackModal();
                alert(response.data.message);
                location.reload();
            } else {
                alert('Error: ' + (response.data?.message || 'Could not save pack.'));
            }
        }).fail(function () {
            alert('Server error. Please try again.');
        }).always(function () {
            $btn.prop('disabled', false).text('Save Pack');
        });
    });

    // Delete pack
    $(document).on('click', '.pca-delete-pack', function (e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this pack?')) return;

        $.post(ajaxurl, {
            action:  'pca_store_delete_pack',
            pack_id: $(this).data('id')
        }, function (response) {
            if (response.success) {
                alert('Pack deleted successfully');
                location.reload();
            } else {
                alert('Error: ' + (response.data?.message || 'Could not delete pack.'));
            }
        }).fail(() => alert('Server error. Please try again.'));
    });

    // Pack row toggle
    $(document).on('click', '.pca-pack-toggle', function (e) {
        e.preventDefault();
        const $btn     = $(this);
        const $children = $('#' + $btn.attr('aria-controls'));
        const isOpen   = $btn.hasClass('open');

        $btn.toggleClass('open', !isOpen)
            .attr('aria-expanded', String(!isOpen))
            .text(isOpen ? '+' : '–');

        $children.stop(true, true).slideToggle(180);
    });

    $('#pca-close-pack-modal').on('click', () => $('#pca-add-pack-modal').hide());


    /* =============================================================
       STATIONERY PACKS
    ============================================================= */

    $(document).on('click', '.pca-add-stationery-to-pack', function (e) {
        e.preventDefault();
        const packId = $(this).data('id');
        $('#pca-pack-stationery-pack-id').val(packId);

        $.post(ajaxurl, { action: 'pca_store_get_stationery_items' }, function (response) {
            if (!response.success) { alert('Could not load stationery items'); return; }

            const list = $('#pca-pack-stationery-list').empty();

            response.data.items.forEach(function (item) {
                list.append(`
                    <tr>
                        <td>${item.name}</td>
                        <td><input type="number" class="pca-stationery-qty" data-id="${item.id}" min="0" value="0"></td>
                    </tr>
                `);
            });

            $('#pca-add-stationery-pack-modal').show();
        });
    });

    $(document).on('click', '#pca-save-stationery-to-pack', function () {
        const packId = $('#pca-pack-stationery-pack-id').val();
        const items  = [];

        $('.pca-stationery-qty').each(function () {
            const qty = parseInt($(this).val());
            if (qty > 0) items.push({ id: $(this).data('id'), qty });
        });

        $.post(ajaxurl, {
            action:  'pca_store_add_stationery_to_pack',
            pack_id: packId,
            items
        }, function (response) {
            alert(response.data.message);
            location.reload();
        });
    });


    /* =============================================================
       SUPPLIERS
    ============================================================= */

    function openSupplierModal(s = null) {
        $('#pca-supplier-id').val(s?.id || '');
        $('#pca-supplier-name').val(s?.name || '');
        $('#pca-supplier-department').val(s?.department_id || '');
        $('#pca-supplier-contact').val(s?.contact_person || '');
        $('#pca-supplier-phone').val(s?.phone || '');
        $('#pca-supplier-email').val(s?.email || '');
        $('#pca-supplier-address').val(s?.address || '');
        $('#pca-supplier-notes').val(s?.notes || '');
        $('#pca-supplier-status').val(s ? (s.is_active ? '1' : '0') : '1');
        $('#pca-supplier-modal-title').text(s ? 'Edit Supplier' : 'Add Supplier');
        $('#pca-add-supplier-modal').show();
    }

    $('#pca-add-supplier-btn').on('click', e => { e.preventDefault(); openSupplierModal(); });

    $('#pca-close-supplier-modal').on('click', e => { e.preventDefault(); $('#pca-add-supplier-modal').hide(); });

    $(document).on('click', '.pca-edit-supplier', function (e) {
        e.preventDefault();
        $.get(ajaxurl, { action: 'pca_get_supplier', id: $(this).data('id') }, function (response) {
            if (!response?.success) { alert(response?.data?.message || 'Error loading supplier'); return; }
            openSupplierModal(response.data.supplier);
        });
    });

    $('#pca-save-supplier').on('click', function (e) {
        e.preventDefault();

        $.post(ajaxurl, {
            action:         'pca_save_supplier',
            id:             $('#pca-supplier-id').val(),
            name:           $('#pca-supplier-name').val(),
            department_id:  $('#pca-supplier-department').val(),
            contact_person: $('#pca-supplier-contact').val(),
            phone:          $('#pca-supplier-phone').val(),
            email:          $('#pca-supplier-email').val(),
            address:        $('#pca-supplier-address').val(),
            notes:          $('#pca-supplier-notes').val(),
            is_active:      $('#pca-supplier-status').val()
        }, function (response) {
            if (!response?.success) { alert(response?.data?.message || 'Error saving supplier'); return; }
            alert(response.data.message || 'Supplier saved');
            window.location.reload();
        });
    });

    $(document).on('click', '.pca-delete-supplier', function (e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this supplier?')) return;

        $.post(ajaxurl, {
            action: 'pca_delete_supplier',
            id:     $(this).data('id')
        }, function (response) {
            if (!response?.success) { alert(response?.data?.message || 'Error deleting supplier'); return; }
            alert(response.data.message || 'Supplier deleted');
            window.location.reload();
        });
    });


    /* =============================================================
       CSV IMPORT
    ============================================================= */

    $(document).on('click', '#pca-import-books-btn', function (e) {
        e.preventDefault();
        $('#pca-import-books-result').html('');
        $('#pca-import-books-modal').show();
    });

    $(document).on('click', '#pca-close-import-books', () => $('#pca-import-books-modal').hide());

    $(document).on('click', '#pca-upload-books-csv', function (e) {
        e.preventDefault();

        const file = $('#pca-books-csv-file')[0].files[0];
        if (!file) { alert('Please select a CSV file.'); return; }

        const formData = new FormData();
        formData.append('action', 'pca_store_import_books');
        formData.append('csv_file', file);

        $('#pca-import-books-result').html('<p><em>Importing… please wait.</em></p>');

        $.ajax({
            url:         ajaxurl,
            type:        'POST',
            data:        formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (!response.success) {
                    $('#pca-import-books-result').html(`<p style="color:red;">${response.data.message}</p>`);
                    return;
                }

                const r = response.data;
                $('#pca-import-books-result').html(`
                    <p><strong>Import Complete</strong></p>
                    <p>Added: ${r.added}</p>
                    <p>Skipped (duplicates): ${r.skipped}</p>
                    <p>Errors: ${r.errors}</p>
                `);

                if (r.added > 0) setTimeout(() => location.reload(), 1500);
            }
        });
    });


    /* =============================================================
       SETTINGS
    ============================================================= */

    $('#pca-save-campus').on('click', function () {
        $.post(ajaxurl, {
            action:    'pca_settings_save_campus',
            name:      $('#pca-campus-name').val(),
            school_id: $('#pca-campus-school').val(),
            status:    $('#pca-campus-status').val()
        }, function (response) {
            alert(response.data.message);
            location.reload();
        });
    });

    $(document).on('click', '.pca-delete-campus', function (e) {
        e.preventDefault();
        if (!confirm('Delete this campus?')) return;

        $.post(ajaxurl, {
            action: 'pca_settings_delete_campus',
            id:     $(this).data('id')
        }, function (response) {
            alert(response.data.message);
            location.reload();
        });
    });

    $('#pca-save-school').on('click', function () {
        $.post(ajaxurl, {
            action: 'pca_settings_save_school',
            name:   $('#pca-school-name').val()
        }, function (response) {
            alert(response.data.message);
            location.reload();
        });
    });

    $(document).on('click', '.pca-delete-school', function (e) {
        e.preventDefault();
        if (!confirm('Delete this school?')) return;

        $.post(ajaxurl, {
            action: 'pca_settings_delete_school',
            id:     $(this).data('id')
        }, function (response) {
            alert(response.data.message);
            location.reload();
        });
    });

    $('#pca-save-department').on('click', function () {
        $.post(ajaxurl, {
            action: 'pca_settings_save_department',
            name:   $('#pca-dept-name').val(),
            code:   $('#pca-dept-code').val()
        }, function (response) {
            alert(response.data.message);
            location.reload();
        });
    });

    $(document).on('click', '.pca-delete-department', function (e) {
        e.preventDefault();
        if (!confirm('Delete this department?')) return;

        $.post(ajaxurl, {
            action: 'pca_settings_delete_department',
            id:     $(this).data('id')
        }, function (response) {
            alert(response.data.message);
            location.reload();
        });
    });

    $('#pca-save-roles').on('click', function () {
        $.post(ajaxurl, {
            action:              'pca_settings_save_roles',
            can_edit_stock:      $('#pca-role-edit-stock').is(':checked')      ? 1 : 0,
            can_view_reports:    $('#pca-role-view-reports').is(':checked')    ? 1 : 0,
            can_manage_settings: $('#pca-role-manage-settings').is(':checked') ? 1 : 0
        }, function (response) {
            alert(response.data.message);
        });
    });

    $('#pca-save-advanced').on('click', function () {
        $.post(ajaxurl, {
            action:     'pca_settings_save_advanced',
            debug_mode: $('#pca-advanced-debug').is(':checked') ? 1 : 0
        }, function (response) {
            alert(response.data.message);
        });
    });

});
