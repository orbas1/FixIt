$(document).ready(function() {
    // Loop through each like-icon div to check the wishlist state
    $('.like-icon').each(function() {
        const iconDiv = $(this);
        const serviceId = iconDiv.data('service-id');
        const providerId = iconDiv.data('provider-id');
        
        // Determine whether we're passing service_id or provider_id
        const data = serviceId ? { service_id: serviceId } : { provider_id: providerId };

        // Check if the item is already in the wishlist
        $.ajax({
            url: '/wishlist/check',
            type: 'GET',
            data: data,  // Send the service_id or provider_id as data
            success: function(response) {
                if (response.isFavourite) {
                    // If the item is a favourite, add the 'active' class to the div
                    iconDiv.addClass('active');
                }
            }
        });
    });

    // Add or remove item from the wishlist on div click
    $('.like-icon').on('click', function(e) {
        e.preventDefault();
        const iconDiv = $(this);
        const serviceId = iconDiv.data('service-id');
        const providerId = iconDiv.data('provider-id');

        // Determine the data to send based on which ID is available
        const data = serviceId ? { service_id: serviceId } : { provider_id: providerId };

        // Check if the item is currently in the wishlist (i.e., has the 'active' class)
        if (iconDiv.hasClass('active')) {
            // If the item is already in the wishlist, remove it
            $.ajax({
                url: '/wishlist/remove',
                type: 'POST',
                data: data, // Send the appropriate data (service_id or provider_id)
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.status === 'removed') {
                        iconDiv.removeClass('active'); // Remove the 'active' class (outline icon will show)
                    }
                }
            });
        } else {
            // If the item is not in the wishlist, add it
            $.ajax({
                url: '/wishlist/add',
                type: 'POST',
                data: data, // Send the appropriate data (service_id or provider_id)
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.status === 'added') {
                        iconDiv.addClass('active'); // Add the 'active' class (fill icon will show)
                    }
                }
            });
        }
    });
});
