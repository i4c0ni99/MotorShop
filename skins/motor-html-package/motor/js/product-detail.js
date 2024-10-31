$(document).ready(function () {
    // Replace placeholders with data from localStorage
    let productId = localStorage.getItem('id');
    let productTitle = localStorage.getItem('titolo');
    let productDescription = localStorage.getItem('descrizione');
    let productPrice = localStorage.getItem('prezzo');
    let productSalePrice = localStorage.getItem('prezzo_scontato');
    let productImages = JSON.parse(localStorage.getItem('immagini'));
    let productSizes = JSON.parse(localStorage.getItem('taglie'));
    let productColors = JSON.parse(localStorage.getItem('colori'));
    let productCategory = localStorage.getItem('categoria');
    let productSubCategory = localStorage.getItem('sotto_categoria');
    let productSpecification = localStorage.getItem('specifiche');
    let productInformation = localStorage.getItem('informazioni');
    let productReviews = JSON.parse(localStorage.getItem('recensioni'));

    $('#product-title').text(productTitle);
    $('#product-description').text(productDescription);
    $('#product-price').text(productPrice + ' €');
    $('#product-sale-price').text(productSalePrice ? productSalePrice + ' €' : '');
    $('#product-specification').text(productSpecification);
    $('#product-information').text(productInformation);
    $('#product-category').text(productCategory);
    $('#product-sub-category').text(productSubCategory);
    $('#product-id').val(productId);
    $('#wishlist-product-id').val(productId);

    // Populate product images
    productImages.forEach((image, index) => {
        $('#product-images').append(`
            <div class="slick-slide">
                <div class="item-img">
                    <img src="${image}" alt="Product Image ${index + 1}" class="block-26-product-image">
                </div>
            </div>
        `);
        $('#product-thumbnails').append(`
            <div class="slick-slide">
                <div class="item-img">
                    <img src="${image}" alt="Product Thumbnail ${index + 1}" class="block-26-product-thumbnail">
                </div>
            </div>
        `);
    });

    // Populate product sizes
    productSizes.forEach(size => {
        $('#product-sizes').append(`
            <li class="item-size">${size}</li>
        `);
    });

    // Populate product colors
    productColors.forEach(color => {
        $('#product-colors').append(`
            <li class="item-color">${color}</li>
        `);
    });

    // Populate product reviews
    productReviews.forEach(review => {
        $('#customer-reviews').append(`
            <div class="mv-review-style-1">
                <div class="mv-dp-table">
                    <div class="mv-dp-table-cell icon"><i class="fa fa-user"></i></div>
                    <div class="mv-dp-table-cell text">
                        <div class="author">${review.author}</div>
                        <div class="text">${review.text}</div>
                    </div>
                </div>
            </div>
        `);
    });

    // Add to cart event
    $('.btn-add-to-cart').click(function () {
        // Add the product to the cart
        alert('Product added to cart');
    });

    // Add to wishlist event
    $('.btn-add-to-wishlist').click(function () {
        // Add the product to the wishlist
        alert('Product added to wishlist');
    });
});
