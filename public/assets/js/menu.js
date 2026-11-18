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

    /* ==========================================================================
       5. Product Detail Modal & Announcement Click Interaction
       ========================================================================== */
    const productModalEl = document.getElementById('productDetailModal');
    let productDetailModal = null;
    if (productModalEl && typeof bootstrap !== 'undefined') {
        productDetailModal = new bootstrap.Modal(productModalEl);
    }

    const modalImg = document.getElementById('modalProductImg');
    const modalImgPlaceholder = document.getElementById('modalProductImgPlaceholder');
    const modalCategoryBadge = document.getElementById('modalProductCategoryBadge');
    const modalName = document.getElementById('modalProductName');
    const modalCode = document.getElementById('modalProductCode');
    const modalPrice = document.getElementById('modalProductPrice');
    const modalVat = document.getElementById('modalProductVat');
    const modalDesc = document.getElementById('modalProductDesc');

    function showProductDetail(data) {
        if (!productDetailModal) return;

        if (modalName) modalName.textContent = data.name || 'Featured Product';
        if (modalCode) {
            modalCode.textContent = data.code ? '#' + data.code : '';
            modalCode.style.display = data.code ? 'inline-block' : 'none';
        }
        if (modalCategoryBadge) {
            modalCategoryBadge.textContent = data.category || data.badge || 'Kaldis Special';
        }
        if (modalDesc) {
            modalDesc.textContent = data.desc || data.message || 'Freshly prepared with authentic Kaldis quality.';
        }

        if (modalPrice) {
            if (data.price) {
                modalPrice.textContent = data.price;
                modalPrice.style.display = 'block';
                if (modalVat) modalVat.style.display = (data.vat === '0' || data.vat === false) ? 'none' : 'block';
            } else {
                modalPrice.textContent = 'Available';
                modalPrice.style.display = 'block';
                if (modalVat) modalVat.style.display = 'none';
            }
        }

        if (modalImg && modalImgPlaceholder) {
            if (data.image) {
                modalImg.src = data.image;
                modalImg.alt = data.name || 'Product Image';
                modalImg.style.display = 'inline-block';
                modalImgPlaceholder.style.display = 'none';
            } else {
                modalImg.src = '';
                modalImg.style.display = 'none';
                modalImgPlaceholder.style.display = 'block';
            }
        }

        productDetailModal.show();
    }

    // Announcement banner item click: navigate to child category and display product
    document.querySelectorAll('.kaldis-moving-banner-item').forEach(item => {
        item.addEventListener('click', function (e) {
            e.preventDefault();

            const pId = this.getAttribute('data-product-id');
            const targetChildId = parseInt(this.getAttribute('data-child-id') || '0', 10);
            const navUrl = this.getAttribute('data-nav-url');
            const currentParams = new URLSearchParams(window.location.search);
            const currentChildId = parseInt(currentParams.get('child') || '0', 10);

            // If product has a child category and user is not currently in that child category,
            // navigate directly to that child category page
            if (navUrl && targetChildId > 0 && targetChildId !== currentChildId) {
                window.location.href = navUrl;
                return;
            }

            const code = (this.getAttribute('data-code') || '').toLowerCase();
            const fallbackData = {
                name: this.getAttribute('data-name') || '',
                price: this.getAttribute('data-price') || '',
                code: this.getAttribute('data-code') || '',
                image: this.getAttribute('data-image') || '',
                badge: this.getAttribute('data-badge') || 'NEW PRODUCT',
                desc: this.getAttribute('data-desc') || this.getAttribute('data-message') || '',
                category: 'New Announcement'
            };

            let targetCardCol = null;
            if (pId) {
                targetCardCol = document.getElementById('prod-card-' + pId) || document.querySelector(`.product-card-col[data-product-id="${pId}"]`);
            }
            if (!targetCardCol && code) {
                targetCardCol = document.querySelector(`.product-card-col[data-code="${code}"]`);
            }

            if (targetCardCol) {
                const card = targetCardCol.querySelector('.product-card');
                const cardData = {
                    name: card ? card.getAttribute('data-name') : fallbackData.name,
                    price: card ? card.getAttribute('data-price') : fallbackData.price,
                    code: card ? card.getAttribute('data-code') : fallbackData.code,
                    image: card ? card.getAttribute('data-image') : fallbackData.image,
                    category: card ? card.getAttribute('data-category') : 'Featured Item',
                    desc: card ? card.getAttribute('data-desc') : fallbackData.desc,
                    vat: card ? card.getAttribute('data-vat') : '1'
                };

                // Switch to products section if currently on categories explore view
                if (categoryCardsSection) categoryCardsSection.style.display = 'none';
                if (productItemsSection) productItemsSection.style.display = 'block';

                // Ensure the target product card is visible (in case filtered by category)
                targetCardCol.style.display = '';

                // Scroll smoothly to target product
                setTimeout(() => {
                    targetCardCol.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    targetCardCol.classList.add('product-card-highlight');
                    setTimeout(() => {
                        targetCardCol.classList.remove('product-card-highlight');
                    }, 4000);
                }, 150);

                // Open product popup modal
                showProductDetail(cardData);
            } else if (navUrl) {
                // If not in DOM but has target URL, navigate to it
                window.location.href = navUrl;
            } else {
                // If product is not directly in the DOM, open modal with announcement data
                showProductDetail(fallbackData);
            }
        });

        // Keyboard accessibility (Enter or Space)
        item.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });

    // Auto-focus and open product if &product=ID is present in URL
    const urlParams = new URLSearchParams(window.location.search);
    const targetProductId = urlParams.get('product');
    if (targetProductId) {
        setTimeout(() => {
            const cardCol = document.getElementById('prod-card-' + targetProductId)
                || document.querySelector(`.product-card-col[data-product-id="${targetProductId}"]`);
            if (cardCol) {
                cardCol.scrollIntoView({ behavior: 'smooth', block: 'center' });
                cardCol.classList.add('product-card-highlight');
                setTimeout(() => {
                    cardCol.classList.remove('product-card-highlight');
                }, 4000);

                const card = cardCol.querySelector('.product-card');
                if (card) {
                    showProductDetail({
                        name: card.getAttribute('data-name'),
                        price: card.getAttribute('data-price'),
                        code: card.getAttribute('data-code'),
                        image: card.getAttribute('data-image'),
                        category: card.getAttribute('data-category'),
                        desc: card.getAttribute('data-desc'),
                        vat: card.getAttribute('data-vat')
                    });
                }
            }
        }, 300);
    }

    // Product cards click: open product detail modal
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', function (e) {
            // Ignore if clicking on external link or button inside card
            if (e.target.closest('a') || e.target.closest('button')) {
                return;
            }
            const data = {
                name: this.getAttribute('data-name') || '',
                price: this.getAttribute('data-price') || '',
                code: this.getAttribute('data-code') || '',
                image: this.getAttribute('data-image') || '',
                category: this.getAttribute('data-category') || 'Menu',
                desc: this.getAttribute('data-desc') || '',
                vat: this.getAttribute('data-vat') || '1'
            };
            showProductDetail(data);
        });

        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });

});
