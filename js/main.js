/**
 * Scottish Mammal Observations Platform
 * Core Client-side Interactivity and Validation Script
 */

document.addEventListener('DOMContentLoaded', () => {
    // --- Contact Form Validation ---
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            let isValid = true;

            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const subjectInput = document.getElementById('subject');
            const messageInput = document.getElementById('message');

            // Helper to show/hide error feedback
            const toggleFeedback = (input, show) => {
                const feedback = input.nextElementSibling;
                if (feedback && feedback.classList.contains('error-feedback')) {
                    feedback.style.display = show ? 'block' : 'none';
                }
                input.style.borderColor = show ? 'var(--color-error)' : 'var(--color-border)';
            };

            // Validate Name
            if (!nameInput.value.trim()) {
                toggleFeedback(nameInput, true);
                isValid = false;
            } else {
                toggleFeedback(nameInput, false);
            }

            // Validate Email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailInput.value.trim() || !emailRegex.test(emailInput.value.trim())) {
                toggleFeedback(emailInput, true);
                isValid = false;
            } else {
                toggleFeedback(emailInput, false);
            }

            // Validate Subject
            if (!subjectInput.value.trim()) {
                toggleFeedback(subjectInput, true);
                isValid = false;
            } else {
                toggleFeedback(subjectInput, false);
            }

            // Validate Message
            if (messageInput.value.trim().length < 10) {
                toggleFeedback(messageInput, true);
                isValid = false;
            } else {
                toggleFeedback(messageInput, false);
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    // --- JavaScript Image Modal Popups ---
    const imageLinks = document.querySelectorAll('.modal-trigger');
    if (imageLinks.length > 0) {
        // Create Modal HTML Elements dynamically
        const modal = document.createElement('div');
        modal.id = 'imgModal';
        modal.style.position = 'fixed';
        modal.style.top = '0';
        modal.style.left = '0';
        modal.style.width = '100vw';
        modal.style.height = '100vh';
        modal.style.backgroundColor = 'rgba(26, 25, 23, 0.95)';
        modal.style.zIndex = '2000';
        modal.style.display = 'none';
        modal.style.justifyContent = 'center';
        modal.style.alignItems = 'center';
        modal.style.cursor = 'zoom-out';
        modal.style.padding = '2rem';
        modal.style.transition = 'opacity 0.3s ease';
        modal.style.opacity = '0';

        const modalImg = document.createElement('img');
        modalImg.style.maxWidth = '90%';
        modalImg.style.maxHeight = '85vh';
        modalImg.style.objectFit = 'contain';
        modalImg.style.borderRadius = 'var(--border-radius-md)';
        modalImg.style.boxShadow = 'var(--shadow-lg)';
        modalImg.style.transition = 'transform 0.3s ease';
        modalImg.style.transform = 'scale(0.95)';

        modal.appendChild(modalImg);
        document.body.appendChild(modal);

        // Add Event Listeners
        imageLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const largeImgUrl = link.getAttribute('href') || link.querySelector('img').src;
                modalImg.src = largeImgUrl;
                
                modal.style.display = 'flex';
                // Trigger reflow for transition
                modal.offsetHeight;
                modal.style.opacity = '1';
                modalImg.style.transform = 'scale(1)';
            });
        });

        // Close modal on click
        modal.addEventListener('click', () => {
            modal.style.opacity = '0';
            modalImg.style.transform = 'scale(0.95)';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        });

        // Close modal on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.style.display === 'flex') {
                modal.click();
            }
        });
    }

    // --- Mobile Hamburger Menu ---
    const navToggle = document.getElementById('navToggle');
    const siteNav = document.querySelector('.site-nav');
    if (navToggle && siteNav) {
        navToggle.addEventListener('click', () => {
            const isActive = siteNav.classList.toggle('active');
            navToggle.classList.toggle('active');
            navToggle.setAttribute('aria-expanded', isActive ? 'true' : 'false');
        });
    }
});
