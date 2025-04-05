jQuery(document).ready(function($) {
    // Event handler for adding a new instance type row
    $(document).on('click', '#add-instance-type', function(e) {
        e.preventDefault();

        // Append a new row to the instance types list
        $('#instance-types-list').append(
            '<tr>' +
                '<td><input type="text" name="dbw-cost-calculator-instance-types[name][]" value="" required /></td>' +
                '<td><input type="text" name="dbw-cost-calculator-instance-types[price][]" value="" required /></td>' +
                '<td><input type="text" name="dbw-cost-calculator-instance-types[link_label][]" value="" /></td>' +
                '<td><input type="text" name="dbw-cost-calculator-instance-types[link_url][]" value="" /></td>' +
                '<td><a class="button button-secondary remove-instance-type" href="#">Remove</a></td>' +
            '</tr>'
        );

        return false;
    });

    // Event handler for adding a new discount rate row
    $(document).on('click', '#add-discount-rate', function(e) {
        e.preventDefault();

        // Append a new row to the discount rates list
        $('#discount-rates-list').append(
            '<tr>' +
                '<td><input type="text" name="dbw-cost-calculator-discount-rates[min_qty][]" value="" required /></td>' +
                '<td><input type="text" name="dbw-cost-calculator-discount-rates[discount][]" value="" required /></td>' +
                '<td><a class="button button-secondary remove-discount-rate" href="#">Remove</a></td>' +
            '</tr>'
        );

        return false;
    });

    // Event handler for adding a new addon row
    $(document).on('click', '#add-addon', function(e) {
        e.preventDefault();

        // Append a new row to the addons list
        $('#addons-list').append(
            '<tr>' +
                '<td><input type="text" name="dbw-cost-calculator-addons[name][]" value="" required /></td>' +
                '<td><input type="text" name="dbw-cost-calculator-addons[price][]" value="" required /></td>' +
                '<td><input type="text" name="dbw-cost-calculator-addons[platforms][]" value="" required /></td>' +
                '<td><input type="text" name="dbw-cost-calculator-addons[link_label][]" value="" /></td>' +
                '<td><input type="text" name="dbw-cost-calculator-addons[link_url][]" value="" /></td>' +
                '<td><a class="button button-secondary remove-discount-rate" href="#">Remove</a></td>' +
            '</tr>'
        );

        return false;
    });

    // Event handler for adding a new recipient row
    $(document).on('click', '#add-recipient', function(e) {
        e.preventDefault();

        // Append a new row to the recipients list
        $('#recipients-list').append(
            '<tr>' +
                '<td><input type="email" name="dbw-cost-calculator-recipients[]" value="" required /></td>' +
                '<td><a class="button button-secondary remove-recipient" href="#">Remove</a></td>' +
            '</tr>'
        );

        return false;
    });

    // Function to remove a row, with an option to prevent removing the last row
    function removeRow($row, deleteLast) {
        if (deleteLast || $row.siblings().length > 0) {
            $row.remove();
        } else {
            alert('Unable to delete the last row.');
        }
    }

    // Event handler for removing instance type or recipient row
    $(document).on('click', '.remove-instance-type, .remove-recipient', function(e) {
        e.preventDefault();
        removeRow($(this).closest('tr'), false);
        return false;
    });

    // Event handler for removing discount rate or addon row
    $(document).on('click', '.remove-discount-rate, .remove-addon', function(e) {
        e.preventDefault();
        removeRow($(this).closest('tr'), true);
        return false;
    });
});