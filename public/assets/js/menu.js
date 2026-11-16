/**
 * Kaldis Coffee PLC — Digital Menu System
 * Interactive Branch Searching, Category Navigation & Real-time Product Filtering
 */

document.addEventListener('DOMContentLoaded', () => {

    /* ==========================================================================
       1. Branch Selection Real-Time Search (Screenshot 1)
       ========================================================================== */
    const branchSearchInput = document.getElementById('branchSearchInput');
    const branchSearchClear = document.getElementById('branchSearchClear');
    const branchCards = document.querySelectorAll('.branch-card-col');
    const branchEmptyState = document.getElementById('branchEmptyState');
    const resetBranchSearch = document.getElementById('resetBranchSearchBtn');

    if (branchSearchInput) {
        branchSearchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            filterBranches(query);
            if (branchSearchClear) {
                branchSearchClear.style.display = query.length > 0 ? 'block' : 'none';
            }
        });

        if (branchSearchClear) {
            branchSearchClear.addEventListener('click', () => {
                branchSearchInput.value = '';
                branchSearchClear.style.display = 'none';
                filterBranches('');
                branchSearchInput.focus();
            });
        }

        if (resetBranchSearch) {
            resetBranchSearch.addEventListener('click', () => {
                branchSearchInput.value = '';
                if (branchSearchClear) branchSearchClear.style.display = 'none';
                filterBranches('');
                branchSearchInput.focus();
            });
        }

        branchSearchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                branchSearchInput.value = '';
                if (branchSearchClear) branchSearchClear.style.display = 'none';
                filterBranches('');
            }
        });
    }

    function filterBranches(query) {
        let visibleCount = 0;
        branchCards.forEach((col) => {
            const name = (col.getAttribute('data-name') || '').toLowerCase();
            const address = (col.getAttribute('data-address') || '').toLowerCase();
            const matches = !query || name.includes(query) || address.includes(query);

            if (matches) {
                col.style.display = '';
                visibleCount++;
            } else {
                col.style.display = 'none';
            }
        });

        if (branchEmptyState) {
            branchEmptyState.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }


    /* ==========================================================================
       2. Menu Category & Product Exploration (Screenshot 2)
       ========================================================================== */
    const productSearchInput = document.getElementById('productSearchInput');
    const productSearchClear = document.getElementById('productSearchClear');
    const categoryCardsSection = document.getElementById('categoryCardsSection');
    const productItemsSection = document.getElementById('productItemsSection');
    const productCards = document.querySelectorAll('.product-card-col');
    const menuEmptyState = document.getElementById('menuEmptyState');

    let debounceTimer;

    if (productSearchInput) {
        productSearchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            const query = e.target.value.toLowerCase().trim();

            if (productSearchClear) {
                productSearchClear.style.display = query.length > 0 ? 'block' : 'none';
            }

            debounceTimer = setTimeout(() => {
                if (query.length > 0) {
                    // Switch to products view when searching
                    if (categoryCardsSection) categoryCardsSection.style.display = 'none';
                    if (productItemsSection) productItemsSection.style.display = 'block';
                    filterProducts(query, null);
                } else {
                    // Check URL parameter: if on root of branch, return to category cards
                    const urlParams = new URLSearchParams(window.location.search);
                    const childParam = parseInt(urlParams.get('child') || '0', 10);
                    const parentParam = parseInt(urlParams.get('parent') || '0', 10);
                    if (childParam === 0 && parentParam === 0) {
                        if (categoryCardsSection) categoryCardsSection.style.display = 'block';
                        if (productItemsSection) productItemsSection.style.display = 'none';
                    } else if (childParam > 0) {
                        filterProducts('', null, childParam);
                    } else {
                        filterProducts('', parentParam, null);
                    }
                }
            }, 80);
        });

        productSearchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                productSearchInput.value = '';
                if (productSearchClear) productSearchClear.style.display = 'none';
                productSearchInput.dispatchEvent(new Event('input'));
            }
        });

        if (productSearchClear) {
            productSearchClear.addEventListener('click', () => {
                productSearchInput.value = '';
                productSearchClear.style.display = 'none';
                productSearchInput.dispatchEvent(new Event('input'));
                productSearchInput.focus();
            });
        }
    }

    function filterProducts(query, categoryId, childId) {
        let visibleCount = 0;

        productCards.forEach((col) => {
            const name = (col.getAttribute('data-name') || '').toLowerCase();
            const desc = (col.getAttribute('data-desc') || '').toLowerCase();
            const code = (col.getAttribute('data-code') || '').toLowerCase();
            const pId = parseInt(col.getAttribute('data-parent-id') || '0', 10);
            const cId = parseInt(col.getAttribute('data-child-id') || '0', 10);

            let matches = true;

            if (query) {
                matches = matches && (name.includes(query) || desc.includes(query) || code.includes(query));
            }

            if (childId && childId > 0) {
                matches = matches && (cId === childId);
            } else if (categoryId && categoryId > 0) {
                matches = matches && (pId === categoryId);
            }

            if (matches) {
                col.style.display = '';
                visibleCount++;
            } else {
                col.style.display = 'none';
            }
        });

        if (menuEmptyState) {
            menuEmptyState.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }


    /* ==========================================================================
       3. Image Fallback Resilience
       ========================================================================== */
    document.querySelectorAll('.product-image, .cat-card-img').forEach((img) => {
        img.addEventListener('error', function () {
            if (this.classList.contains('product-image')) {
                const container = this.closest('.product-image-container');
                if (container) {
                    this.style.display = 'none';
                    if (!container.querySelector('.product-placeholder-icon')) {
                        const icon = document.createElement('i');
                        icon.className = 'bi bi-cup-hot product-placeholder-icon';
                        container.appendChild(icon);
                    }
                }
            } else if (this.classList.contains('cat-card-img')) {
                // Fallback warm brown gradient if category image fails
                this.style.display = 'none';
            }
        });
    });

    /* ==========================================================================
       4. Moving Message Banner Mobile Touch Control
       ========================================================================== */
    const movingBannerTrack = document.querySelector('.kaldis-moving-banner-track');
    if (movingBannerTrack) {
        let touchResumeTimer;
        movingBannerTrack.addEventListener('touchstart', () => {
            clearTimeout(touchResumeTimer);
            movingBannerTrack.classList.add('is-paused');
        }, { passive: true });

        movingBannerTrack.addEventListener('touchend', () => {
            touchResumeTimer = setTimeout(() => {
                movingBannerTrack.classList.remove('is-paused');
            }, 1200);
        }, { passive: true });
    }

});
