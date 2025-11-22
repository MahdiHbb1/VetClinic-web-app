// Owner Portal JavaScript
$(document).ready(function() {
    // Smooth scroll
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $(this.getAttribute('href'));
        if(target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 80
            }, 500);
        }
    });

    // Add fade-in animation to cards with delay
    $('.fade-in').each(function(i) {
        setTimeout(() => {
            $(this).css('opacity', '1');
        }, i * 100);
    });

    // Mobile menu toggle
    $('#mobile-menu-button').on('click', function() {
        $('#mobile-menu').toggleClass('hidden');
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert-auto-dismiss').fadeOut('slow');
    }, 5000);

    // Confirm logout
    $('a[href*="logout"]').on('click', function(e) {
        if (!confirm('Are you sure you want to logout?')) {
            e.preventDefault();
        }
    });
});
