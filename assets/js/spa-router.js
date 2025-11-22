/**
 * VetClinic SPA Router
 * Single Page Application navigation system for fast, seamless page transitions
 * No more full page reloads - load content once and navigate instantly
 */

(function() {
    'use strict';

    // ===========================
    // CONFIGURATION
    // ===========================
    const config = {
        contentContainerId: 'main-content',
        sidebarId: 'sidebar',
        loadingClass: 'spa-loading',
        transitionDuration: 200,
        cachePages: true
    };

    // Page cache for instant navigation
    const pageCache = new Map();

    // ===========================
    // CORE ROUTER
    // ===========================
    class SPARouter {
        constructor() {
            this.currentPath = window.location.pathname;
            this.isNavigating = false;
            this.init();
        }

        init() {
            // Intercept all internal links
            this.attachLinkHandlers();

            // Handle browser back/forward buttons
            window.addEventListener('popstate', (e) => {
                if (e.state && e.state.path) {
                    this.loadPage(e.state.path, false);
                }
            });

            // Save initial state
            history.replaceState({ path: this.currentPath }, '', this.currentPath);

            console.log('✓ SPA Router initialized - Fast navigation enabled');
        }

        attachLinkHandlers() {
            // Delegate click events for better performance
            document.addEventListener('click', (e) => {
                const link = e.target.closest('a[href]');
                
                if (!link) return;

                const href = link.getAttribute('href');
                
                // Skip external links, downloads, and special links
                if (this.shouldSkipLink(link, href)) {
                    return;
                }

                // Intercept and handle with SPA router
                e.preventDefault();
                this.navigate(href);
            });
        }

        shouldSkipLink(link, href) {
            // Skip if:
            return (
                !href || // No href
                href.startsWith('#') || // Anchor links
                href.startsWith('javascript:') || // JS links
                href.startsWith('mailto:') || // Email links
                href.startsWith('tel:') || // Phone links
                link.getAttribute('target') === '_blank' || // New tab
                link.hasAttribute('download') || // Downloads
                link.closest('.no-spa') || // Marked to skip SPA
                href.includes('/auth/logout.php') || // Logout needs full reload
                href.includes('/auth/login.php') || // Login needs full reload
                href.includes('landing.php') // Landing page needs full reload
            );
        }

        shouldCachePage(path) {
            // Don't cache pages with dynamic content that requires fresh script execution
            const noCachePaths = [
                '/dashboard/',
                '/inventory/report.php',
                '/owners/portal/pet_profile.php'
            ];
            
            // Check if path matches any no-cache pattern
            return !noCachePaths.some(pattern => path.includes(pattern));
        }

        navigate(path) {
            if (this.isNavigating || path === this.currentPath) {
                return;
            }

            this.loadPage(path, true);
        }

        async loadPage(path, pushState = true) {
            this.isNavigating = true;

            try {
                // Show loading state
                this.showLoadingState();

                // Determine if page should be cached
                const shouldCache = this.shouldCachePage(path);

                // Check cache first
                let html;
                if (config.cachePages && shouldCache && pageCache.has(path)) {
                    html = pageCache.get(path);
                } else {
                    // Fetch page content via AJAX
                    html = await this.fetchPageContent(path);
                    
                    // Cache the result only if appropriate
                    if (config.cachePages && shouldCache) {
                        pageCache.set(path, html);
                    }
                }

                // Update content with smooth transition
                await this.updateContent(html);

                // Update browser history
                if (pushState) {
                    history.pushState({ path: path }, '', path);
                }

                // Update sidebar active state
                this.updateSidebarActiveState(path);

                // Update current path
                this.currentPath = path;

                // Trigger page loaded event
                this.triggerPageLoaded(path);

                // Performance logging
                console.log(`✓ Page loaded: ${path} (from ${config.cachePages && pageCache.has(path) ? 'cache' : 'network'})`);

            } catch (error) {
                console.error('❌ Navigation error:', error);
                this.showErrorState();
                
                // Fallback to full page load on error
                window.location.href = path;
            } finally {
                this.isNavigating = false;
                this.hideLoadingState();
            }
        }

        async fetchPageContent(path) {
            const response = await fetch(path, {
                headers: {
                    'X-SPA-Request': 'true' // Signal this is SPA request
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const html = await response.text();

            // Extract main content from full HTML
            return this.extractMainContent(html);
        }

        extractMainContent(html) {
            // Create temporary DOM to parse
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Get the main content container
            const mainContent = doc.querySelector('main') || 
                               doc.querySelector('#main-content') ||
                               doc.querySelector('.main-container > div > main');

            if (!mainContent) {
                // If no main tag, try to get content area
                const container = doc.querySelector('.container');
                return container ? container.innerHTML : html;
            }

            return mainContent.innerHTML;
        }

        async updateContent(html) {
            const container = document.querySelector('main') || 
                            document.querySelector('#main-content');

            if (!container) {
                console.error('Content container not found');
                return;
            }

            // Fade out current content
            container.style.opacity = '0';
            container.style.transition = `opacity ${config.transitionDuration}ms ease`;

            // Wait for fade out
            await new Promise(resolve => setTimeout(resolve, config.transitionDuration));

            // Update content
            container.innerHTML = html;

            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });

            // Re-initialize scripts for new content
            this.reinitializeScripts(container);

            // Fade in new content
            setTimeout(() => {
                container.style.opacity = '1';
            }, 10);
        }

        reinitializeScripts(container) {
            // Destroy existing Chart.js instances to prevent memory leaks
            if (typeof Chart !== 'undefined' && Chart.instances) {
                Object.keys(Chart.instances).forEach(key => {
                    const instance = Chart.instances[key];
                    if (instance && typeof instance.destroy === 'function') {
                        instance.destroy();
                    }
                });
            }

            // Re-initialize DataTables if present
            const tables = container.querySelectorAll('.datatable');
            if (tables.length > 0 && typeof DataTable !== 'undefined') {
                tables.forEach(table => {
                    if (!table.classList.contains('dataTable')) {
                        try {
                            new DataTable(table, {
                                pageLength: 10,
                                language: {
                                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                                },
                                responsive: true
                            });
                        } catch (e) {
                            console.warn('DataTable initialization failed:', e);
                        }
                    }
                });
            }

            // Re-execute inline scripts with proper timing
            // Use setTimeout to ensure DOM is fully ready
            setTimeout(() => {
                const scripts = container.querySelectorAll('script');
                scripts.forEach(oldScript => {
                    // Skip external scripts (they're already loaded globally)
                    if (oldScript.src) return;
                    
                    try {
                        // Create new script element
                        const newScript = document.createElement('script');
                        newScript.textContent = oldScript.textContent;
                        
                        // Replace old script with new one to trigger execution
                        if (oldScript.parentNode) {
                            oldScript.parentNode.replaceChild(newScript, oldScript);
                        }
                    } catch (e) {
                        console.warn('Script execution failed:', e);
                    }
                });

                // Reinitialize dark mode toggle state
                this.reinitializeDarkMode();

                // Trigger DOMContentLoaded for new content
                const event = new Event('DOMContentLoaded');
                document.dispatchEvent(event);
            }, 50); // Small delay to ensure DOM is ready
        }

        reinitializeDarkMode() {
            // Ensure dark mode toggle reflects current theme state
            const savedTheme = localStorage.getItem('theme');
            const toggle = document.getElementById('darkModeToggle');
            
            if (savedTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
                if (document.body) {
                    document.body.setAttribute('data-theme', 'dark');
                }
                if (toggle) {
                    toggle.classList.add('active');
                }
            } else {
                document.documentElement.removeAttribute('data-theme');
                if (document.body) {
                    document.body.removeAttribute('data-theme');
                }
                if (toggle) {
                    toggle.classList.remove('active');
                }
            }
        }

        updateSidebarActiveState(path) {
            const sidebar = document.getElementById(config.sidebarId);
            if (!sidebar) return;

            // Remove all active classes
            const links = sidebar.querySelectorAll('a');
            links.forEach(link => {
                link.classList.remove('bg-blue-50', 'text-blue-600');
                link.classList.add('text-gray-700');
            });

            // Find and activate matching link
            const activeLink = Array.from(links).find(link => {
                const href = link.getAttribute('href');
                // Match exact path or path prefix for sections
                return href === path || 
                       (href !== '/' && path.startsWith(href) && href.length > 1);
            });

            if (activeLink) {
                activeLink.classList.remove('text-gray-700');
                activeLink.classList.add('bg-blue-50', 'text-blue-600');
            }
        }

        showLoadingState() {
            document.body.classList.add(config.loadingClass);
            
            // Show progress bar animation
            const progressBar = document.getElementById('spaProgressBar');
            if (progressBar) {
                progressBar.style.width = '0%';
                progressBar.style.display = 'block';
                
                // Animate to 90% (will complete on load)
                setTimeout(() => {
                    progressBar.style.width = '70%';
                }, 50);
            }
            
            // Show loading spinner for slower connections
            this.spinnerTimeout = setTimeout(() => {
                const spinner = document.getElementById('loadingSpinner');
                if (spinner && this.isNavigating) {
                    spinner.classList.remove('hidden');
                    spinner.classList.add('flex');
                }
            }, 300); // Only show spinner if takes > 300ms
        }

        hideLoadingState() {
            document.body.classList.remove(config.loadingClass);
            
            // Clear spinner timeout
            if (this.spinnerTimeout) {
                clearTimeout(this.spinnerTimeout);
            }
            
            // Complete progress bar
            const progressBar = document.getElementById('spaProgressBar');
            if (progressBar) {
                progressBar.style.width = '100%';
                
                // Hide after completion
                setTimeout(() => {
                    progressBar.style.display = 'none';
                    progressBar.style.width = '0%';
                }, 400);
            }
            
            // Hide loading spinner
            const spinner = document.getElementById('loadingSpinner');
            if (spinner) {
                spinner.classList.remove('flex');
                spinner.classList.add('hidden');
            }
        }

        showErrorState() {
            if (typeof showToast === 'function') {
                showToast('Gagal memuat halaman. Silakan coba lagi.', 'error');
            } else {
                alert('Gagal memuat halaman. Silakan coba lagi.');
            }
        }

        triggerPageLoaded(path) {
            // Custom event for other scripts to hook into
            const event = new CustomEvent('spa:pageLoaded', { 
                detail: { path: path } 
            });
            document.dispatchEvent(event);
        }

        // Public API
        clearCache() {
            pageCache.clear();
            console.log('✓ Page cache cleared');
        }

        prefetchPage(path) {
            // Prefetch page for instant navigation
            if (!pageCache.has(path)) {
                this.fetchPageContent(path).then(html => {
                    pageCache.set(path, html);
                    console.log(`✓ Prefetched: ${path}`);
                });
            }
        }
    }

    // ===========================
    // INITIALIZE ROUTER
    // ===========================
    let router;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            router = new SPARouter();
            window.spaRouter = router; // Expose globally
        });
    } else {
        router = new SPARouter();
        window.spaRouter = router;
    }

    // ===========================
    // PREFETCHING STRATEGY
    // ===========================
    // Prefetch pages on hover for instant navigation
    document.addEventListener('mouseover', (e) => {
        const link = e.target.closest('a[href]');
        if (link && router && !router.shouldSkipLink(link, link.getAttribute('href'))) {
            const href = link.getAttribute('href');
            if (href && href.startsWith('/') && !href.startsWith('//')) {
                router.prefetchPage(href);
            }
        }
    });

    // ===========================
    // DEVELOPER TOOLS
    // ===========================
    // Add global helper functions for debugging
    window.spaDebug = {
        clearCache: () => {
            if (router) {
                router.clearCache();
                console.log('✓ SPA cache cleared');
            }
        },
        showCache: () => {
            console.log('📦 Cached pages:', pageCache.size);
            pageCache.forEach((value, key) => {
                console.log(`  - ${key}`);
            });
        },
        disableCache: () => {
            config.cachePages = false;
            console.log('✓ SPA cache disabled');
        },
        enableCache: () => {
            config.cachePages = true;
            console.log('✓ SPA cache enabled');
        },
        reload: () => {
            if (router) {
                router.clearCache();
                location.reload();
            }
        }
    };

    // Log initialization
    console.log('%c🚀 VetClinic SPA Mode Active', 'color: #3b82f6; font-weight: bold; font-size: 14px');
    console.log('%cℹ️ Type spaDebug.showCache() to see cached pages', 'color: #6b7280; font-size: 12px');

})();
