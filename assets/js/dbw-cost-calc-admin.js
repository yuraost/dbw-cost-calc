jQuery(document).ready(function($){
    $(document).on('click', '#add-instance-type', function(e){
        e.preventDefault();

        $('#instance-types-list').append(
            '<tr>' +
                '<td><input type="text" name="dbw-cost-calculator-instance-types[name][]" value="" required /></td>' +
                '<td><input type="text" name="dbw-cost-calculator-instance-types[price][]" value="" required /></td>' +
                '<td><a class="button button-secondary remove-instance-type" href="#">Remove</a></td>' +
            '</tr>'
        );

        return false;
    });

    $(document).on('click', '#add-discount-rate', function(e){
        e.preventDefault();

        $('#discount-rates-list').append(
            '<tr>' +
                '<td><input type="text" name="dbw-cost-calculator-discount-rates[min_qty][]" value="" required /></td>' +
                '<td><input type="text" name="dbw-cost-calculator-discount-rates[discount][]" value="" required /></td>' +
                '<td><a class="button button-secondary remove-discount-rate" href="#">Remove</a></td>' +
            '</tr>'
        );

        return false;
    });

    $(document).on('click', '#add-addon', function(e){
        e.preventDefault();

        $('#addons-list').append(
            '<tr>' +
                '<td><input type="text" name="dbw-cost-calculator-addons[name][]" value="" required /></td>' +
                '<td><input type="text" name="dbw-cost-calculator-addons[price][]" value="" required /></td>' +
                '<td><input type="text" name="dbw-cost-calculator-addons[platforms][]" value="" required /></td>' +
                '<td><a class="button button-secondary remove-discount-rate" href="#">Remove</a></td>' +
            '</tr>'
        );

        return false;
    });

    function removeRow($row, deleteLast) {
        if (deleteLast || $row.siblings().length > 0) {
            $row.remove();
        } else {
            alert('Unable to delete the last row.');
        }
    }

    $(document).on('click', '.remove-instance-type', function(e){
        e.preventDefault();
        removeRow($(this).closest('tr'), false);
        return false;
    });

    $(document).on('click', '.remove-discount-rate, .remove-addon', function(e){
        e.preventDefault();
        removeRow($(this).closest('tr'), true);
        return false;
    });
});