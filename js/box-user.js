document.addEventListener('DOMContentLoaded', function() {
    const userProfileArea = document.getElementById('userProfileArea'); // The .user div in navbar
    const profileHoverBox = document.getElementById('profileHoverBox'); // The hover box element

    if (!userProfileArea || !profileHoverBox) {
        // If elements don't exist (e.g., user not logged in), exit
        return;
    }

    let showTimeout;
    let hideTimeout;
    const delay = 200; // milliseconds for hover out delay

    function getCssVariable(variableName) {
        return getComputedStyle(document.documentElement).getPropertyValue(variableName);
    }

    function positionHoverBox() {
        const userRect = userProfileArea.getBoundingClientRect(); // Get position of the .user div in navbar
        const spaceL = parseFloat(getCssVariable('--space-l')) || 24; // Default to 24px if not found

        // Calculate target right: Align right edge of hover box with right edge of userProfileArea
        // Then add var(--space-l) margin from the right edge of the viewport
        const targetRight = window.innerWidth - userRect.right + spaceL; 

        // Calculate target top: Align top of hover box with top of userProfileArea
        // Then push it down by var(--space-l)
        const targetTop = userRect.top + userRect.height + spaceL; // Di bawah userRect dengan jarak space-l

        profileHoverBox.style.top = `${targetTop}px`;
        profileHoverBox.style.right = `${targetRight}px`;
    }

    function showHoverBoxDelayed() {
        clearTimeout(hideTimeout);
        showTimeout = setTimeout(() => {
            positionHoverBox(); // Position just before showing
            profileHoverBox.style.opacity = '1';
            profileHoverBox.style.visibility = 'visible';
            profileHoverBox.style.pointerEvents = 'auto'; // Enable interactions
            profileHoverBox.style.transform = 'translateY(0)';
        }, 50); // Slightly reduced delay for showing, make it feel snappier
    }

    function hideHoverBoxDelayed() {
        clearTimeout(showTimeout);
        hideTimeout = setTimeout(() => {
            profileHoverBox.style.opacity = '0';
            profileHoverBox.style.visibility = 'hidden';
            profileHoverBox.style.pointerEvents = 'none'; // Disable interactions
            profileHoverBox.style.transform = 'translateY(-10px)'; // Animate slightly up
        }, delay);
    }

    // Event listeners
    // Trigger on the parent area to ensure smooth hover transition between elements
    userProfileArea.addEventListener('mouseenter', showHoverBoxDelayed);
    userProfileArea.addEventListener('mouseleave', hideHoverBoxDelayed);

    // Keep the box visible if mouse moves into it
    profileHoverBox.addEventListener('mouseenter', () => {
        clearTimeout(hideTimeout); // Cancel pending hide if mouse re-enters box
    });
    profileHoverBox.addEventListener('mouseleave', hideHoverBoxDelayed);

    // Reposition on window resize (important for fixed/absolute elements)
    window.addEventListener('resize', () => {
        if (profileHoverBox.style.visibility === 'visible') {
            positionHoverBox();
        }
    });

    // Initial positioning in case the page loads with the box somehow visible
    positionHoverBox();
});