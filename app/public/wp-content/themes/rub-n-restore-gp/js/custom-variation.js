jQuery(document).ready(function($) {
  // Listen for the 'show_variation' event on variation forms
  $('.variations_form').on('show_variation', function(event, variation) {
    // Check if the variation has a price
    if (variation.price_html) {
      // Update the button text with the price and "Add to Cart"
      $('.single_add_to_cart_button').html(variation.price_html + ' &ndash; ' + wc_custom_params.add_to_cart_text);

      // Clear the previous price and prepend the new price to the .product-details__options element
      var $productDetailsOptions = $('.product-details__options');
      $productDetailsOptions.find('.variation-price').remove();
      $productDetailsOptions.prepend('<div class="variation-price">' + variation.price_html + '</div>');
    }
  });
});

/* Add click event to "How much do I need?" text to scroll to FAQ section */

document.addEventListener('DOMContentLoaded', (event) => {
  // Select the link by ID or any query selector
  const link = document.getElementById('learn-more-about-faq');

  // Add click event listener
  link.addEventListener('click', function(e) {
    // Prevent the default anchor click behavior
    e.preventDefault();

    // Get the href attribute of the clicked link
    const targetId = this.getAttribute('href').slice(1); // remove the '#' at the beginning

    // Find the target element by ID
    const targetElement = document.getElementById(targetId);

    // Make sure the element exists
    if (targetElement) {
      // Scroll to the target element smoothly
      targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });

      // Find the link within the target element
      const innerLink = targetElement.querySelector('a');

      // If there's a link, click it
      if (innerLink) {
        setTimeout(() => innerLink.click(), 0); // Timeout ensures the click event fires after the scroll
      }
    }
  });
});

