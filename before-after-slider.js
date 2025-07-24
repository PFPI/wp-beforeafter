document.addEventListener('DOMContentLoaded', function() {
    // Select all slider containers on the page
    const sliders = document.querySelectorAll('.cd-image-container');

    sliders.forEach(slider => {
        const handle = slider.querySelector('.cd-handle');
        const resizeWrapper = slider.querySelector('.cd-resize-img');
        let isDragging = false;

        // Function to update the slider position
        function updateSliderPosition(x) {
            const sliderRect = slider.getBoundingClientRect();
            // Calculate position relative to the slider container
            let position = x - sliderRect.left;

            // Constrain the position within the bounds of the slider
            if (position < 0) position = 0;
            if (position > sliderRect.width) position = sliderRect.width;
            
            // Convert the position to a percentage
            let percent = (position / sliderRect.width) * 100;
            
            // Apply the width to the resizing wrapper and the left position to the handle
            handle.style.left = percent + '%';
            resizeWrapper.style.width = percent + '%';
        }

        // --- Mouse Events ---
        slider.addEventListener('mousedown', function(e) {
            e.preventDefault();
            isDragging = true;
        });

        document.addEventListener('mouseup', function() {
            isDragging = false;
        });

        document.addEventListener('mousemove', function(e) {
            if (!isDragging || !e.target.closest('.cd-image-container')) return;
            // We request animation frame for smoother performance
            window.requestAnimationFrame(() => {
                updateSliderPosition(e.clientX);
            });
        });

        // --- Touch Events ---
        slider.addEventListener('touchstart', function(e) {
            isDragging = true;
        }, { passive: true });

        document.addEventListener('touchend', function() {
            isDragging = false;
        });

        document.addEventListener('touchmove', function(e) {
            if (!isDragging || !e.target.closest('.cd-image-container')) return;
            // Prevent page scrolling while dragging the slider
            e.preventDefault();
            window.requestAnimationFrame(() => {
                updateSliderPosition(e.touches[0].clientX);
            });
        }, { passive: false });
    });
});