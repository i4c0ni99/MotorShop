document.addEventListener("DOMContentLoaded", function () {
    // Function to get URL parameters
    function getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }

    // Check if 'subid' is present in the URL
    function isSubIdPresent() {
        return getUrlParameter('subId') !== '';
    }

    // Function to update URL with parameters
    function updateUrlParameter(key, value) {
        var baseUrl = [location.protocol, '//', location.host, location.pathname].join('');
        var urlQueryString = document.location.search;
        var newParam = key + '=' + value;
        var params = '?' + newParam;

        // If the "search" string exists, then build params from it
        if (urlQueryString) {
            var updateRegex = new RegExp('([?&])' + key + '[^&]*');
            var removeRegex = new RegExp('([?&])' + key + '=[^&;]+[&;]?');

            if (typeof value === 'undefined' || value === null || value === '') { // Remove param if value is empty
                params = urlQueryString.replace(removeRegex, "$1");
                params = params.replace(/[&;]$/, "");

            } else if (urlQueryString.match(updateRegex) !== null) { // If param exists already, update it
                params = urlQueryString.replace(updateRegex, "$1" + newParam);

            } else { // Otherwise, add it to end of query string
                params = urlQueryString + '&' + newParam;
            }
        }

        // Navigate to the URL
        history.pushState(null, '', baseUrl + params);
    }

    // Function to handle select change events
    function handleSelectChange() {
        var size = document.getElementById('select-size').value;
        var color = document.getElementById('select-color').value;

        // Update URL with 'subid' parameter only if both size and color are selected
        if (size && color) {
            updateUrlParameter('subid', size + '-' + color);
        } else {
            updateUrlParameter('subid', '');
        }

        // Check if 'subid' is now present and show/hide elements accordingly
        if (isSubIdPresent()) {
            document.getElementById('quantity-row').style.display = 'table-row'; // Assuming 'quantity-row' is the ID of the table row containing quantity
            document.querySelectorAll('.btn-add-to-cart, .btn-add-to-wishlist').forEach(function(element) {
                element.style.display = 'inline-block'; // Assuming these are the classes for add to cart and add to wishlist buttons
            });
        } else {
            document.getElementById('quantity-row').style.display = 'none';
            document.querySelectorAll('.btn-add-to-cart, .btn-add-to-wishlist').forEach(function(element) {
                element.style.display = 'none';
            });
        }

        // Reload the page to reflect changes in URL
        location.reload();
    }

    // Attach change event listeners to select elements
    document.getElementById('select-size').addEventListener('change', handleSelectChange);
    document.getElementById('select-color').addEventListener('change', handleSelectChange);

    // Initial check to hide quantity and buttons if 'subid' is not present
    if (!isSubIdPresent()) {
        document.getElementById('quantity-row').style.display = 'none';
        document.querySelectorAll('.btn-add-to-cart, .btn-add-to-wishlist').forEach(function(element) {
            element.style.display = 'none';
        });
    }

    // Replace placeholders with data from localStorage (assuming this part is already correct as per previous example)

    // Add to cart event
    document.querySelector('.btn-add-to-cart').addEventListener('click', function () {
        // Add the product to the cart
        alert('Product added to cart');
    });

    // Add to wishlist event
    document.querySelector('.btn-add-to-wishlist').addEventListener('click', function () {
        // Add the product to the wishlist
        alert('Product added to wishlist');
    });
});
