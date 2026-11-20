/**
 * TrombinoscopeContacts Ajax search
 *
 * @copyright Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license GNU General Public License version 3 or later; see LICENSE.txt
 */

class TrombinoscopeContactsAjax {
    constructor(options = {}) {
        this.moduleId = options.moduleId || 0;
        this.baseUrl = options.baseUrl || '';
        this.token = options.token || '';
        this.container = options.container || '#trombinoscope-contacts';
        this.loadingClass = options.loadingClass || 'loading';

        this.minLength = 2;
        this.searchTimeout = null;

        this.init();
    }

    init() {
        const list = document.querySelector(`${this.container} .personlist`);
        if (list) {
            this.originalHtml = list.innerHTML;
        }

        const searchInput = document.querySelector(`${this.container} .contact-search`);
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => {
                    this.onSearch(e.target.value.trim());
                }, 300); // Debounce search
            });
        }
    }

    /**
     * Handle a search box change: search, or restore the original list if
     * the term was cleared/too short.
     */
    async onSearch(term) {
        if (term.length < this.minLength) {
            this.restoreOriginal();
            return;
        }

        const html = await this.searchContacts(term);
        if (html !== null) {
            this.renderHtml(html);
        }
    }

    /**
     * Restore the list as it was before any search was made.
     */
    restoreOriginal() {
        const list = document.querySelector(`${this.container} .personlist`);
        if (list && typeof this.originalHtml === 'string') {
            list.innerHTML = this.originalHtml;
        }
    }

    /**
     * Replace the contacts list with server-rendered card HTML.
     */
    renderHtml(html) {
        const list = document.querySelector(`${this.container} .personlist`);
        if (list) {
            list.innerHTML = html;
        }
    }

    /**
     * Ajax request to search contacts by name.
     *
     * @return {Promise<string|null>} the rendered HTML, or null on failure
     */
    async searchContacts(term) {
        const params = {
            method: 'searchContacts',
            module_id: this.moduleId,
            q: term,
            [this.token]: '1'
        };

        try {
            this.showLoading();

            const response = await fetch(`${this.baseUrl}index.php?option=com_ajax&module=trombinoscope&format=json`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(params)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                return data.data.html;
            } else {
                throw new Error(data.message || 'Unknown error occurred');
            }
        } catch (error) {
            console.error('Ajax search failed:', error);
            this.showError('Search failed. Please try again.');
            return null;
        } finally {
            this.hideLoading();
        }
    }

    showLoading() {
        const container = document.querySelector(this.container);
        if (container) {
            container.classList.add(this.loadingClass);
        }
    }

    hideLoading() {
        const container = document.querySelector(this.container);
        if (container) {
            container.classList.remove(this.loadingClass);
        }
    }

    showError(message) {
        const container = document.querySelector(this.container);
        if (container) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger';
            errorDiv.textContent = message;
            container.insertBefore(errorDiv, container.firstChild);

            // Auto-hide after 5 seconds
            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        }
    }
}

// Auto-initialize every module instance with Ajax search enabled
document.addEventListener('DOMContentLoaded', function() {
    const ajaxModules = document.querySelectorAll('[data-trombinoscope-ajax]');

    ajaxModules.forEach(moduleElement => {
        const moduleData = JSON.parse(moduleElement.getAttribute('data-trombinoscope-ajax'));
        new TrombinoscopeContactsAjax(moduleData);
    });
});
