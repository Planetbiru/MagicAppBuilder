/**
 * Manages the frontend application for interacting with the GraphQL API.
 * Handles entity navigation, data rendering (lists, details, forms), and user interactions.
 */
class GraphQLClientApp {
    /**
     * Initializes the GraphQL client application.
     * @param {object} [options={}] - Configuration options for the application.
     * @param {object} [options.customRenderers] - Custom renderer hooks for entities (e.g., for list, detail, form views).
     * @param {string} [options.defaultActiveField='active'] - Default field name for the 'active' status column.
     */
    constructor(options = {}) {
        const defaults = {
            configUrl: 'frontend-config.php',
            apiUrl: 'graphql.php',
            loginUrl: 'login.php',
            logoutUrl: 'logout.php',
            entityLanguageUrl: 'entity-language.php',
            i18nUrl: 'language.php',
            languageConfigUrl: 'available-language.php',

            customRenderers: {},
            defaultActiveField: 'active',
            defaultDisplayField: 'name',

            languageId: null,
            
            defaultLanguage: 'en',
        };

        Object.assign(this, defaults, options);

        this.supportedLanguages = {};


        // i18n for UI elements outside the class
        /** @type {object<string, string>} Translations for UI elements. */
        this.uiTranslations = {};

        /** @type {object} Stores language packs for entities, keyed by language ID. */
        this.entityLanguagePack = {};
        /** @type {?object} The main application configuration loaded from the server. */
        this.config = null;
        /** @type {?object} The currently active entity's configuration. */
        this.currentEntity = null;
        /**
         * The current state of the list view.
         * @type {{
         *   page: number,
         *   limit: number,
         *   filters: object<string, string>,
         *   orderBy: {field?: string, direction?: 'ASC'|'DESC'}
         * }}
         */
        this.state = {
            page: 1,
            limit: 10,
            filters: {}, // Changed to an object {field: value}
            orderBy: {}, // Changed to an object {field, direction}
        };
        this.i18n = {};
        
        /** @type {object<string, HTMLElement>} A map of cached DOM elements. */
        this.dom = {
            menu: document.getElementById('entity-menu'),
            title: document.getElementById('content-title'),
            body: document.getElementById('content-body'),
            modal: document.getElementById('form-modal'),
            modalTitle: document.getElementById('modal-title'),
            form: document.getElementById('entity-form'),
            closeModalBtn: document.querySelector('.close-button'),
            loginModal: document.getElementById('login-modal'),
            loginForm: document.getElementById('login-form'),
            loginCloseBtn: document.getElementById('login-close-button'),
            logoutBtn: document.querySelector('.logout-link'),
            logoutBtnDropdown: document.getElementById('logout-btn-dropdown'),
            sidebarToggle: document.getElementById('sidebar-toggle'),
            sidebar: document.getElementById('sidebar-nav'),
            mainContent: document.getElementById('main-content'),
            langMenu: document.getElementById('lang-menu'),
            filterContainer: document.getElementById('filter-container'),
            tableDataContainer: document.getElementById('table-data-container'),
            paginationContainer: document.getElementById('pagination-container'),
            loadingBar: document.getElementById('loading-bar'),
            themeToggle: document.getElementById('theme-toggle'),
            pageWrapper: document.getElementById('page-wrapper'),
            infoModal: document.getElementById('infoModal'),
            infoModalTitle: document.getElementById('infoModalTitle'),
            infoModalMessage: document.getElementById('infoModalMessage'),
            infoModalOk: document.getElementById('infoModalOk'),
        };
        this.init();
    }

    /**
     * Initializes page-specific UI elements and event listeners, such as the sidebar and dropdowns.
     * @returns {void}
     */
    initPage() {
        // Sidebar toggle functionality with animation control
        if (this.dom.sidebarToggle && this.dom.sidebar && this.dom.mainContent) {
            // When the toggle is clicked, update the class on the <html> element and save the state
            this.dom.sidebarToggle.addEventListener('click', () => {
                const isCollapsed = document.documentElement.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            });
        }

        // Add the animation class after the initial render to prevent animation on page load.
        this.dom.sidebar.classList.add('sidebar-animated');

        // Dropdown menu functionality
        document.querySelectorAll('[data-dropdown]').forEach(button => {
            button.addEventListener('click', (event) => {
                event.stopPropagation(); // Prevent click from bubbling up to the window
                const dropdownId = button.dataset.dropdown;
                const targetDropdown = document.getElementById(dropdownId);

                if (targetDropdown) {
                    // Close other active dropdowns before opening a new one
                    document.querySelectorAll('.dropdown-menu.active').forEach(openDropdown => {
                        if (openDropdown !== targetDropdown) {
                            openDropdown.classList.remove('active');
                        }
                    });
                    // Toggle the clicked dropdown
                    targetDropdown.classList.toggle('active');
                }
            });
        });

        // Add a listener to the window to close dropdowns when clicking outside
        window.addEventListener('click', () => {
            document.querySelectorAll('.dropdown-menu.active').forEach(dropdown => dropdown.classList.remove('active'));
        });

        // Ensure logout link in dropdown works
        if (this.dom.logoutBtnDropdown) {
            this.dom.logoutBtnDropdown.onclick = (e) => this.handleLogout(e);
        }

        // Language selection handler
        this.dom.langMenu.addEventListener('click', (e) => {
            if (e.target.matches('a[data-lang]')) {
                e.preventDefault();
                const lang = e.target.dataset.lang;
                this.changeLanguage(lang);
            }
        });

        // Theme toggle handler
        this.dom.themeToggle.addEventListener('click', () => this.toggleTheme());

        // Listen for theme changes in other tabs
        window.addEventListener('storage', (event) => {
            if (event.key === 'theme') {
                this.applyTheme(event.newValue);
            }
        });

        // Apply initial theme
        const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        this.applyTheme(savedTheme);

    }

    /**
     * Handles 401 Unauthorized responses by clearing the UI and showing the login modal.
     * This is typically triggered when the user's session has expired.
     * @returns {void}
     */
    handleUnauthorized() {
        // Check if the login modal is already visible
        if (this.dom.loginModal.style.display === 'block') {
            return; // Do nothing if login modal is already open
        }

        // Hide the entire page wrapper and show the login modal
        this.dom.pageWrapper.style.display = 'none';
        document.getElementById('login-error').textContent = this.t('session_expired');
        this.openLoginModal();
    }

    /**
     * Asynchronously initializes the application by loading configuration, language packs, and setting up event listeners.
     * This is the main entry point for the application logic.
     * @returns {Promise<void>} Resolves when initialization completes.
     */
    async init() {
        let _this = this;

        this.dom.loginCloseBtn.onclick = () => this.closeLoginModal();
        this.dom.loginForm.onsubmit = (e) => this.handleLogin(e);
        this.dom.logoutBtn.onclick = (e) => this.handleLogout(e);

        try {
            await this.initializeLanguage();
            await this.loadI18n();
            await this.loadConfig();
            await this.loadLanguage();
            this.applyI18n();
            this.initPage(); // Initialize UI event listeners
            window.onclick = (event) => {
                if (event.target == this.dom.modal) this.closeModal();
            };
            // Add event listener for data-dismiss="modal"
            document.addEventListener('click', (event) => {
                const dismissButton = event.target.closest('[data-dismiss="modal"]');
                if (dismissButton) {
                    const modal = dismissButton.closest('.modal');
                    if (modal) {
                        if (modal.id === this.dom.modal.id) {
                            this.closeModal();
                        }
                        else if (modal.id === this.dom.loginModal.id) {
                            this.closeLoginModal();
                        }
                        else if (modal.id === this.dom.infoModal.id) {
                            // This is handled by the customAlert promise resolver
                            // but we can also explicitly close it.
                            modal.classList.remove('show');
                        }
                        else {
                            modal.classList.remove('show');
                        }
                    }
                }
            });

            this.buildMenu();


            // Handle initial page load and back/forward button clicks
            window.addEventListener('popstate', () => this.handleRouteChange());
            this.handleRouteChange(); // Handle initial route
        } catch (error) {
            console.error('Initialization Error:', error);
            this.dom.body.innerHTML = `<p style="color: red;">Error: ${error.message}</p>`; // NOSONAR
        }
    }

    /**
     * Invokes a custom render hook if it exists for a given action and entity.
     * @param {string} action - The action being performed (e.g., 'list', 'detail', 'form').
     * @param {object} context - The context object for the hook.
     * @param {object} context.entity - The current entity configuration.
     * @param {HTMLElement} context.container - The DOM element to render into.
     * @param {object|Array} [context.data] - The data for rendering.
     * @param {string|number} [context.id] - The ID of the item.
     * @returns {boolean} - True if the custom hook was executed, false otherwise.
     */
    // Method remains unchanged
    _invokeRenderHook(action, context) {
        const entityName = context.entity.name;
        if (this.customRenderers[entityName] && typeof this.customRenderers[entityName][action] === 'function') {
            this.customRenderers[entityName][action](context);
            return true;
        }
        return false;
    }

    /**
     * Determines the language to use, fetches language configuration, and populates the language menu.
     * It checks local storage, then browser preferences, before falling back to the default.
     * @returns {Promise<void>} Resolves when language configuration and menu are initialized.
     */
    async initializeLanguage() {
        try {
            const response = await fetch(this.languageConfigUrl);
            if (!response.ok) throw new Error(`Could not fetch ${this.languageConfigUrl}`);
            const langConfig = await response.json();
            this.supportedLanguages = langConfig.supported;
            this.defaultLanguage = langConfig.default;

            const savedLang = localStorage.getItem('userLanguage');
            let langToLoad = this.defaultLanguage;

            if (savedLang && this.supportedLanguages[savedLang]) {
                langToLoad = savedLang;
            } else {
                const browserLang = navigator.language.split('-')[0];
                if (this.supportedLanguages[browserLang]) {
                    langToLoad = browserLang;
                }
            }
            this.languageId = langToLoad;
            localStorage.setItem('userLanguage', this.languageId);
            localStorage.setItem('languageId', this.languageId); // Sync for both systems
            document.documentElement.lang = this.languageId;

            // Now populate the menu, so the active language can be marked correctly.
            this.populateLangMenu();

        } catch (error) {
            console.error("Failed to initialize language configuration, falling back to default:", error);
            // Fallback to default language if config fails to load
            this.supportedLanguages = { 'en': 'English' };
            this.defaultLanguage = 'en';
            
            // Set language to default
            this.languageId = this.defaultLanguage;
            localStorage.setItem('userLanguage', this.languageId);
            localStorage.setItem('languageId', this.languageId); // Sync for both systems
            document.documentElement.lang = this.languageId;

            // Populate menu even on fallback
            this.populateLangMenu();
        }
    }

    /**
     * Populates the language dropdown menu based on the supported languages.
     * This is called after the language configuration is loaded.
     * @returns {void}
     */
    populateLangMenu() {
        this.dom.langMenu.innerHTML = ''; // Clear existing items
        for (const [code, name] of Object.entries(this.supportedLanguages)) {
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = '#';
            a.dataset.lang = code;
            if (code === this.languageId) {
                a.classList.add('active');
            }
            a.textContent = name;
            li.appendChild(a);
            this.dom.langMenu.appendChild(li);
        }
    }

    /**
     * Fetches and loads the main application configuration from the specified URL.
     * This configuration defines entities, columns, filters, etc.
     * @returns {Promise<void>} Resolves when configuration is loaded and applied.
     */
    async loadConfig() {
        const response = await fetch(this.configUrl);
        if (response.status === 401) {
            this.handleUnauthorized();
            throw new Error("Authentication required.");
        }
        if (!response.ok) throw new Error(`Failed to load config from ${this.configUrl}`); // NOSONAR
        this.config = await response.json();

        if (this.config.pagination && this.config.pagination.pageSize) {
            this.state.limit = this.config.pagination.pageSize;
        }
    }

    /**
     * Fetches the language pack for the current language ID.
     * It restructures the received JSON data before storing it.
     * The final structure maps an entity's translated name to its translated column names.
     * Example: `this.entityLanguagePack['id']['service'] = { service_id: 'Id Layanan', ... }`
     * @throws {Error} If the language file fails to load.
     * @returns {Promise<void>}
     */
    async loadLanguage() {
        try {
            if (this.entityLanguageUrl) {
                let url = `${this.entityLanguageUrl}?lang=${this.languageId}`;
                const response = await fetch(url);
                if (response.status === 401) {
                    this.handleUnauthorized();
                    throw new Error("Authentication required.");
                }
                if (!response.ok) {
                    throw new Error(`Failed to load language from ${this.entityLanguageUrl}`); // NOSONAR
                }
                let data = await response.json();
                this.entityLanguagePack[this.languageId] = {};
                for (let name in data.entities) {
                    this.entityLanguagePack[this.languageId][name] = data.entities[name].columns;
                }
            }
        } catch (error) {
            console.warn(`Could not load entity language file for '${this.languageId}'. Falling back to generated labels.`, error);
            // The getEntityLabel method will automatically fall back to snakeCaseToTitleCase
        }
    }

    /**
     * Fetches the general language pack for the UI.
     * This is used for translating static UI elements like buttons and labels.
     * @returns {Promise<void>} Resolves when the i18n pack has been loaded (or fallen back to an empty pack).
     */
    async loadI18n() {
        try {
            if (this.i18nUrl) {
                let url = `${this.i18nUrl}?lang=${this.languageId}`;
                const response = await fetch(url);
                if (response.status === 401) {
                    this.handleUnauthorized();
                    throw new Error("Authentication required.");
                }
                if (!response.ok) {
                    throw new Error(`Failed to load i18n from ${this.i18nUrl}`); // NOSONAR
                }
                this.i18n = await response.json();
            }
        } catch (error) {
            console.warn("Could not load language file. Falling back to key-based labels.", error);
            this.i18n = {}; // Ensure i18n is an empty object on failure
        }
    }

    /**
     * Applies translations to all elements with a `data-i18n` attribute.
     * This should be called after `loadI18n` and whenever the DOM is updated with new translatable elements.
     * @returns {void}
     */
    applyI18n() {
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                el.placeholder = this.t(key);
            } else {
                el.textContent = this.t(key);
            }
        });
        document.querySelectorAll('[data-i18n-title]').forEach(el => {
            const key = el.getAttribute('data-i18n-title');
            el.title = this.t(key);
        });
    }

    /**
     * Translates a given key using the loaded i18n pack.
     * If a translation is not found, it generates a label from the key.
     * Supports simple placeholders like {0}, {1}.
     * @param {string} key - The key to translate.
     * @param {...(string|number)} args - Values to replace placeholders.
     * @param  {...any} args - Values to replace placeholders.
     * @returns {string} The translated string or a generated label.
     */
    t(key, ...args) {
        let translatedText = this.i18n[key];

        if (translatedText) {
            // If translation exists, replace placeholders
            args.forEach((arg, index) => {
                const placeholder = new RegExp(`\\{${index}\\}`, 'g');
                translatedText = translatedText.replace(placeholder, arg);
            });
            return translatedText;
        }

        // If no translation, generate from key and append arguments
        const baseText = this._generateLabelFromKey(key);
        return [baseText, ...args].join(' ');
    }


    /**
     * Changes the application language and reloads the page.
     * This ensures all components, including Summernote, re-initialize with the correct language.
     * The new language is saved to local storage.
     * @param {string} lang - The new language ID (e.g., 'en', 'id').
     * @returns {void}
     */
    changeLanguage(lang) {
        localStorage.setItem('userLanguage', lang);
        localStorage.setItem('languageId', lang); // Set both for consistency
        window.location.reload();
    }

    /**
     * Toggles the color theme between 'light' and 'dark'.
     * The selected theme is saved to local storage.
     * @returns {void}
     */
    toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        this.applyTheme(newTheme);
        localStorage.setItem('theme', newTheme);
    }

    /**
     * Applies a specific theme to the application.
     * It sets a `data-theme` attribute on the `<html>` element.
     * @param {string} theme - The theme to apply ('light' or 'dark').
     * @returns {void}
     */
    applyTheme(theme) {
        // Simply set the data-theme attribute. CSS will handle showing/hiding the correct icon path.
        document.documentElement.setAttribute('data-theme', theme);
    }

    /**
     * Gets the translated label for a given entity and property key.
     * It looks for translations in the loaded entity language pack.
     * Falls back to a title-cased version of the key if no translation is found.
     * @param {object} entity - The entity configuration object.
     * @param {string} key - The property key (column name) to get the label for.
     * @returns {string} The translated label or a formatted key.
     */
    getEntityLabel(entity, key) { // NOSONAR
        let entityName = this.snakeCase(entity.name);
        if (this.entityLanguagePack && this.entityLanguagePack[this.languageId]) {
            let entityLanguage = this.entityLanguagePack[this.languageId][entity.name];
            if (!entityLanguage) {
                entityLanguage = this.entityLanguagePack[this.languageId][entityName];
            }
            if (entityLanguage && entityLanguage[key]) {
                return entityLanguage[key];
            }
            return this.snakeCaseToTitleCase(key);
        } else {
            return this.snakeCaseToTitleCase(key);
        }
    }
    /**
     * Gets the user's preferred languages from the browser's navigator settings.
     * @private
     * @returns {string[]} An array of language codes, sorted by preference.
     */
    getPreferredLanguages() {
        if (navigator.languages && navigator.languages.length) {
            return navigator.languages;
        } else if (navigator.language) {
            return [navigator.language]; // fallback
        } else {
            return ['en']; // default
        }
    }

    /**
     * Builds the main navigation menu based on the entities defined in the configuration.
     * Each menu item links to the list view of an entity.
     * @returns {void}
     */
    buildMenu() {
        if (!this.config || !this.config.entities) return;
        this.dom.menu.innerHTML = '';
        Object.values(this.config.entities).forEach((entity, index) => {
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = `#${entity.name}`;
            a.textContent = entity.displayName;
            // Reset filters and order when a menu item is clicked
            a.onclick = (e) => {
                e.preventDefault();
                this.navigateTo(entity.name, { limit: this.state.limit });
            };
            li.appendChild(a);
            this.dom.menu.appendChild(li);
        });
    }

    /**
     * Sends a GraphQL query to the API endpoint.
     * It handles loading indicators, authorization errors, and GraphQL-specific errors.
     * @param {string} query - The GraphQL query string.
     * @param {object} [variables={}] - The variables for the query.
     * @returns {Promise<object>} The data from the GraphQL response.
     */
    async gqlQuery(query, variables = {}) {
        this.dom.loadingBar.style.display = 'block';
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'xmlhttprequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ query, variables }),
            });

            if (response.status === 401) {
                this.handleUnauthorized();
                throw new Error("Authentication required."); // NOSONAR
            }

            const result = await response.json();

            if (result.errors) {
                console.error('GraphQL Errors:', result.errors);
                console.error(`Error: ${result.errors[0].message}`);
                throw new Error(result.errors[0].message);
            }
            return result.data;
        } catch (error) {
            // Re-throw the error so that calling functions can handle it
            throw error;
        } finally {
            // This block will always run, whether the request succeeds or fails
            this.dom.loadingBar.style.display = 'none';
        }
    }

    // =================================================================
    // RENDER METHODS
    // =================================================================

    /**
     * Navigates to the list view of a specific entity and updates the URL.
     * This method updates the browser history.
     * @param {string} entityName - The name of the entity to navigate to.
     * @param {object} [params={}] - URL parameters like page and limit.
     * @returns {void}
     */
    navigateTo(entityName, params = {}) {
        // When navigating, we usually want to reset to a clean state unless specified otherwise.
        const currentFilters = params.filters || {};
        const currentOrderBy = params.orderBy || {};

        const newParams = new URLSearchParams();
        newParams.set('page', params.page || 1);
        newParams.set('limit', params.limit || 10);

        Object.entries(currentFilters).forEach(([key, value]) => { if (value) newParams.set(key, value); });
        if (currentOrderBy.field) {
            newParams.set('orderBy', currentOrderBy.field);
            newParams.set('orderDir', currentOrderBy.direction);
        }
        const newUrl = `${window.location.pathname}#${entityName}?${newParams.toString()}`;
        history.pushState({ entityName, params }, '', newUrl);
        this.handleRouteChange();
    }

    /**
     * Navigates to the detail view of a specific item and updates the URL.
     * This method updates the browser history.
     * @param {string} entityName - The name of the entity.
     * @param {string|number} id - The ID of the item to display.
     * @returns {void}
     */
    navigateToDetail(entityName, id) {
        const newUrl = `${window.location.pathname}#${entityName}/detail/${id}`;
        history.pushState({ entityName, id }, '', newUrl);
        this.handleRouteChange();
    }

    /**
     * Handles routing based on the URL hash. It parses the entity, view type, and parameters
     * from the hash and calls the appropriate render method.
     * This is the central point for client-side routing.
     * @returns {Promise<void>} Resolves when the view rendering completes.
     */
    async handleRouteChange() {
        const hash = window.location.hash.substring(1);
        if (!hash || hash === '#') {
            this.renderDashboardView();
            return;
        }

        const [path, queryString] = hash.split('?');
        const pathParts = path.split('/');

        const entityName = pathParts[0];
        const viewType = pathParts.length > 1 ? pathParts[1] : 'list';
        const itemId = pathParts.length > 2 ? pathParts[2] : null;

        const params = new URLSearchParams(queryString);

        const entity = this.config.entities[entityName];
        if (!entity) {
            this.dom.body.innerHTML = `<p>Entity "${entityName}" not found.</p>`;
            return;
        }

        this.currentEntity = entity;

        const filters = {};
        for (const [key, value] of params.entries()) {
            if (key !== 'page' && key !== 'limit') {
                filters[key] = value;
            }
        }

        const orderBy = {};
        if (params.has('orderBy')) {
            orderBy.field = params.get('orderBy');
            orderBy.direction = params.get('orderDir') || 'ASC';
        }

        // Handle limit parameter correctly, allowing for a value of 0 to be processed.
        const limitParam = params.get('limit');
        const newLimit = limitParam !== null ? parseInt(limitParam, 10) : (this.state.limit || 10);

        this.state = {
            page: parseInt(params.get('page')) || 1,
            limit: newLimit,
            filters: filters,
            orderBy: orderBy,
        };

        // Enforce min/max page size from config
        if (this.config && this.config.pagination) {
            const { minPageSize, maxPageSize } = this.config.pagination;
            if (minPageSize && this.state.limit < minPageSize) {
                this.state.limit = minPageSize;
            }
            if (maxPageSize && this.state.limit > maxPageSize) {
                this.state.limit = maxPageSize;
            }
        }

        document.querySelectorAll('#entity-menu a').forEach(link => link.classList.toggle('active', link.getAttribute('href') === `#${entityName}`));

        if (viewType === 'detail' && itemId) {
            this.dom.filterContainer.style.display = 'none';
            this.clearListView();
            await this.renderDetailView(itemId);
        } else {
            this.dom.filterContainer.style.display = 'block';
            await this.renderListView(); // Render the static parts of the list view
            await this.updateTableView(); // Then fetch and render the dynamic data
        }
    }

    /**
     * Renders the default dashboard/welcome view.
     * This is shown when no entity is selected.
     * @returns {void}
     */
    renderDashboardView() {
        // Set title and clear any active menu items
        this.dom.title.textContent = this.t('dashboard');
        document.querySelectorAll('#entity-menu a').forEach(link => link.classList.remove('active'));

        // Hide filter container and clear table/pagination
        this.dom.filterContainer.style.display = 'none';
        this.clearListView();

        // Display welcome message
        this.dom.tableDataContainer.innerHTML = `<p>${this.t('welcome_dashboard')}</p>`;
    }

    /**
     * Renders the static parts of the list view, such as the title and filter controls.
     * The dynamic table data is rendered separately by `updateTableView`.
     * @returns {Promise<void>} Resolves when the filters have been rendered.
     */
    async renderListView() {
        this.clearListView();
        this.dom.title.textContent = this.t('list_of', this.currentEntity.displayName);
        await this.renderFilters(); // Render filters and controls once
    }

    /**
     * Fetches data based on the current state (filters, pagination) and updates
     * the table and pagination sections of the view.
     * @returns {Promise<void>} Resolves when the view is updated.
     */
    async updateTableView() {
        const fields = this.getFieldsForQuery(this.currentEntity, 1); // depth 1
        const offset = (this.state.page - 1) * this.state.limit;

        // Construct filter array for GQL query from state object
        const filterForQuery = [];
        if (this.currentEntity.filters) {
            Object.entries(this.state.filters).forEach(([field, value]) => {
                const filterConfig = this.currentEntity.filters.find(f => f.name === field);
                if (value && filterConfig) {
                    filterForQuery.push({ field: field, value: value, operator: filterConfig.operator || 'EQUALS' });
                }
            });
        }

        // Construct orderBy array for GQL query
        const orderByForQuery = [];
        if (this.state.orderBy && this.state.orderBy.field) {
            orderByForQuery.push(this.state.orderBy);
        }


        const query = `
            query Get${this.currentEntity.pluralName}($limit: Int, $offset: Int, $orderBy: [SortInput], $filter: [FilterInput]) {
                ${this.currentEntity.pluralName}(limit: $limit, offset: $offset, orderBy: $orderBy, filter: $filter) {
                    items { ${fields} }
                    total
                    limit
                    page
                    totalPages
                    hasNext
                    hasPrevious
                }
            }
        `;

        try {
            this.dom.tableDataContainer.innerHTML = this.t('loading');
            this.dom.paginationContainer.innerHTML = '';

            const data = await this.gqlQuery(query, {
                limit: this.state.limit,
                offset: offset,
                orderBy: orderByForQuery,
                filter: filterForQuery,
            });
            const result = data[this.currentEntity.pluralName];
            this.renderTable(result.items); // Renders into tableDataContainer
            this.renderPagination(result);
        } catch (error) {
            this.dom.tableDataContainer.innerHTML = `<p style="color: red;">${error.message}</p>`;
        }
    }

    /**
     * Renders the HTML table with data for the current entity.
     * It also attaches event listeners for action buttons and sortable headers.
     * @param {Array<object>} items - The array of items to display in the table.
     * @returns {void}
     */
    renderTable(items) {
        let _this = this;
        const headers = Object.keys(this.currentEntity.columns);
        let tableHtml = `
            <div class="table-container">

                <table class="table table-data-list table-striped">
                    <thead>
                        <tr>
                            ${headers.map(h => {
            const isSortable = true; // Assume all are sortable for now
            const isCurrentSort = this.state.orderBy.field === h;
            const sortIcon = isCurrentSort ? (this.state.orderBy.direction === 'ASC' ? ' &#9650;' : ' &#9660;') : '';
            return `<th class="${isSortable ? 'sortable' : ''}" data-sort-key="${h}">
                                            ${this.getEntityLabel(this.currentEntity, h)}${sortIcon}
                                        </th>`;
        }).join('')} 
                            <th>${this.t('actions')}</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        if (items.length === 0) {
            tableHtml += `<tr><td colspan="${headers.length + 1}">${this.t('no_items_found')}</td></tr>`;
        } else {
            items.forEach(item => {
                let activeField = this.currentEntity.activeField || this.defaultActiveField;
                let isActive = true;
                if (this.currentEntity.hasActiveColumn) {
                    isActive = item[activeField] === 1 || item[activeField] === '1' || item[activeField] === true;
                }

                tableHtml += `<tr${isActive ? '' : ' class="inactive"'}>`;
                headers.forEach(header => {
                    const col = this.currentEntity.columns[header];
                    let value = item[header];
                    let relationName = col.references;


                    if (col.isForeignKey && item[relationName]) {
                        const relatedEntity = this.config.entities[this.camelCase(relationName)];
                        const displayField = relatedEntity ? relatedEntity.displayField : this.defaultDisplayField;
                        value = item[relationName] ? item[relationName][displayField] : 'N/A';
                    } else if ((col.type.includes('boolean') || header === this.currentEntity.activeField) && this.config.booleanDisplay) {
                        const isTrue = value === 1 || value === '1' || value === true;
                        if (isTrue) {
                            value = this.t(this.config.booleanDisplay.trueLabelKey);
                        } else {
                            value = this.t(this.config.booleanDisplay.falseLabelKey);
                        }
                    }

                    tableHtml += `<td>${value !== null ? value : 'N/A'}</td>`;
                });
                tableHtml += this.renderActionButtons(item);
                tableHtml += `</tr>`;
            });
        }

        tableHtml += `</tbody></table></div>`;
        this.dom.tableDataContainer.innerHTML = tableHtml;

        document.querySelectorAll('.btn-detail').forEach(btn => btn.onclick = (e) => this.navigateToDetail(this.currentEntity.name, e.currentTarget.dataset.id));
        document.querySelectorAll('.btn-edit').forEach(btn => btn.onclick = (e) => this.renderForm(e.currentTarget.dataset.id));
        document.querySelectorAll('.btn-delete').forEach(btn => btn.onclick = (e) => this.handleDelete(e.currentTarget.dataset.id));
        document.querySelectorAll('.btn-toggle-active').forEach(btn => btn.onclick = (e) => this.handleToggleActive(e.currentTarget.dataset.id, e.currentTarget.dataset.active === 'true'));

        // Add sort handlers to table headers
        document.querySelectorAll('th.sortable').forEach(th => {
            th.style.cursor = 'pointer';
            th.onclick = () => this.handleSort(th.dataset.sortKey);
        });
    }

    /**
     * Renders filter controls and the "Add New" button based on the current entity's configuration.
     * Attaches event listeners for the search button and for the 'Enter' key on filter inputs.
     * For select filters, it pre-fetches data for the dropdown options.
     * @returns {Promise<void>}
     */
    async renderFilters() {
        const hasFilters = this.currentEntity.filters && this.currentEntity.filters.length > 0;
        let filterHtml = '';

        if (hasFilters) {
            filterHtml += '<div class="filter-controls">';
            for (const filter of this.currentEntity.filters) {
                const currentValue = this.state.filters[filter.name] || '';
                filterHtml += `<div class="form-group">`;
                filterHtml += `<label for="filter-${filter.name}">${this.getEntityLabel(this.currentEntity, filter.name)}</label>`;

                if (filter.element === 'select') {
                    const col = this.currentEntity.columns[filter.name];
                    if (!col || !col.isForeignKey) {
                        console.error(`Filter "${filter.name}" is of type 'select' but no corresponding foreign key column was found in the entity configuration.`);
                        continue;
                    }
                    const relatedEntity = this.config.entities[this.camelCase(col.references)];
                    const relatedData = relatedEntity ? (await this.fetchAll(relatedEntity)) : [];
                    const displayField = relatedEntity.displayField || 'name';

                    filterHtml += `<select id="filter-${filter.name}" name="${filter.name}">`;
                    filterHtml += `<option value="">${this.t('select_option')}</option>`;
                    relatedData.forEach(relItem => {
                        const relId = relItem[relatedEntity.primaryKey];
                        const relDisplay = relItem[displayField];
                        filterHtml += `<option value="${relId}" ${String(relId) === String(currentValue) ? 'selected' : ''}>${relDisplay}</option>`;
                    });
                    filterHtml += `</select>`;
                } else { // Default to text input
                    filterHtml += `<input type="text" id="filter-${filter.name}" name="${filter.name}" value="${currentValue}" placeholder="${this.getEntityLabel(this.currentEntity, filter.name)}">`;
                }
                filterHtml += `</div>`;
            }
            filterHtml += `<button id="search-btn" class="btn btn-primary">${this.t('search')}</button>`;
            filterHtml += `<button id="reset-filter-btn" class="btn btn-secondary">${this.t('reset_filter')}</button>`;
        }

        // Add the "Add New" button. Inside .filter-controls if filters exist, otherwise directly in .filter-container.
        filterHtml += `<button id="add-new-btn" class="btn btn-primary">${this.t('add_new', this.currentEntity.displayName)}</button>`;

        if (hasFilters) {
            filterHtml += '</div>'; // Close .filter-controls
        }

        this.dom.filterContainer.innerHTML = filterHtml;

        if (hasFilters) {
            document.getElementById('search-btn').onclick = () => this.handleSearch();
            document.getElementById('reset-filter-btn').onclick = () => this.handleResetFilters();

            // Add keydown listener to trigger search on Enter key
            this.dom.filterContainer.querySelectorAll('.filter-controls input, .filter-controls select').forEach(input => {
                input.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault(); // Prevent default form submission behavior
                        this.handleSearch();
                    }
                });
            });
        }
        document.getElementById('add-new-btn').onclick = () => this.renderForm();
    }

    /**
     * Clears the content of the main view containers (filter, table, pagination).
     * This is used before rendering a new view.
     * @returns {void}
     */
    clearListView() {
        this.dom.filterContainer.innerHTML = '';
        this.dom.tableDataContainer.innerHTML = '';
        this.dom.paginationContainer.innerHTML = '';
    }

    /**
     * Handles the search action. It collects filter values, updates the application state, and refreshes the data view.
     * It resets the page to 1 for the new search.
     * @returns {void}
     */
    handleSearch() {
        const filters = {};
        this.dom.filterContainer.querySelectorAll('input, select').forEach(input => {
            filters[input.name] = input.value;
        });

        // Update state and URL without full page reload
        this.state.page = 1;
        this.state.filters = filters;
        this.updateUrlForState();
        this.updateTableView(); // Only update the data table and pagination
    }

    /**
     * Handles the filter reset action. It clears all filters and sorting, then navigates to the clean list view.
     * It resets the page to 1.
     * @returns {void}
     */
    handleResetFilters() {
        // Clear visual inputs
        this.dom.filterContainer.querySelectorAll('input, select').forEach(input => {
            input.value = '';
        });
        // Reset internal state for filters and sorting, and go to page 1
        this.state.filters = {};
        this.state.orderBy = {};
        this.state.page = 1;
        this.updateUrlForState();
        this.updateTableView();
    }

    /**
     * Handles the sort action when a table header is clicked.
     * It toggles the sort direction or sets a new sort field.
     * @param {string} field - The field name to sort by.
     */
    handleSort(field) {
        let newDirection = 'ASC';
        if (this.state.orderBy.field === field && this.state.orderBy.direction === 'ASC') {
            newDirection = 'DESC';
        }

        this.state.orderBy = {
            field: field,
            direction: newDirection
        };

        // Go back to page 1 when sorting changes
        this.state.page = 1;

        this.updateUrlForState();
        this.updateTableView();
    }

    /**
     * Renders the action buttons (e.g., View, Edit, Delete) for a table row.
     * It includes a toggle active button if the entity supports it.
     * @param {object} item - The data item for the row.
     * @returns {string} The HTML string for the action buttons cell.
     */
    renderActionButtons(item) {
        const id = item[this.currentEntity.primaryKey]; // NOSONAR
        let buttons = `<td class="actions">`;
        buttons += `<button class="btn btn-sm btn-info btn-detail" data-id="${id}">${this.t('view')}</button> `;
        buttons += `<button class="btn btn-sm btn-warning btn-edit" data-id="${id}">${this.t('edit')}</button> `;

        if (this.currentEntity.hasActiveColumn) {
            const activeField = this.currentEntity.activeField || this.defaultActiveField;

            const isActive = item[activeField] === 1 || item[activeField] === '1' || item[activeField] === true;

            buttons += `<button class="btn btn-sm ${isActive ? 'btn-secondary' : 'btn-success'} btn-toggle-active" data-id="${id}" data-active="${isActive}">${isActive ? this.t('deactivate') : this.t('activate')}</button> `;
        }
        buttons += `<button class="btn btn-sm btn-danger btn-delete" data-id="${id}">${this.t('delete')}</button></td>`;
        return buttons;
    }

    /**
     * Renders the pagination controls based on the query result.
     * It includes page information and previous/next buttons.
     * @param {object} result - The pagination data from the GraphQL response.
     * @returns {void}
     */
    renderPagination(result) {
        if (!result || result.total === 0) {
            this.dom.paginationContainer.innerHTML = '';
            return;
        }

        const prevPage = this.state.page - 1;
        const nextPage = this.state.page + 1;

        const buildPageUrl = (pageNumber) => {
            const params = new URLSearchParams();
            params.set('page', pageNumber);
            params.set('limit', this.state.limit);
            Object.entries(this.state.filters).forEach(([key, value]) => { if (value) params.set(key, value); });
            if (this.state.orderBy.field) {
                params.set('orderBy', this.state.orderBy.field);
                params.set('orderDir', this.state.orderBy.direction);
            }
            return `${window.location.pathname}#${this.currentEntity.name}?${params.toString()}`;
        };

        const prevUrl = result.hasPrevious ? buildPageUrl(prevPage) : '#';
        const nextUrl = result.hasNext ? buildPageUrl(nextPage) : '#';

        this.dom.paginationContainer.innerHTML = `
            <span>${this.t('page_of', result.page, result.totalPages, result.total)}</span>
            <a id="prev-page" href="${prevUrl}" class="btn btn-secondary ${!result.hasPrevious ? 'disabled' : ''}">${this.t('previous')}</a>
            <a id="next-page" href="${nextUrl}" class="btn btn-secondary ${!result.hasNext ? 'disabled' : ''}">${this.t('next')}</a>
        `;

        const handleNavClick = (e, newPage) => {
            // Allow middle-click, right-click, and ctrl/cmd-click to open in new tab
            if (e.button !== 0 || e.ctrlKey || e.metaKey) {
                return;
            }
            e.preventDefault();
            if (e.currentTarget.classList.contains('disabled')) return;

            this.state.page = newPage;
            this.updateUrlForState();
            this.updateTableView();
        }

        document.getElementById('prev-page').addEventListener('click', (e) => handleNavClick(e, prevPage));
        document.getElementById('next-page').addEventListener('click', (e) => handleNavClick(e, nextPage));
    }

    /**
     * Updates the URL in the browser's address bar to reflect the current application state (page, filters).
     * This method updates the browser history.
     * @returns {void}
     */
    updateUrlForState() {
        const newParams = new URLSearchParams();
        newParams.set('page', this.state.page);
        newParams.set('limit', this.state.limit);
        Object.entries(this.state.filters).forEach(([key, value]) => { if (value) newParams.set(key, value); });
        if (this.state.orderBy.field) {
            newParams.set('orderBy', this.state.orderBy.field);
            newParams.set('orderDir', this.state.orderBy.direction);
        }
        const newUrl = `${window.location.pathname}#${this.currentEntity.name}?${newParams.toString()}`;
        history.pushState({ entityName: this.currentEntity.name, params: this.state }, '', newUrl);
    }

    /**
     * Fetches and renders the detail view for a single item.
     * It can be overridden by a custom renderer hook.
     * @param {string|number} id - The ID of the item to display.
     * @returns {Promise<void>} Resolves when the detail view has been rendered.
     */
    async renderDetailView(id) {
        this.dom.title.textContent = this.t('detail_of', this.currentEntity.displayName); // NOSONAR

        // Panggil hook kustom untuk render detail
        const hookHandled = this._invokeRenderHook('detail', {
            entity: this.currentEntity,
            container: this.dom.body,
            id: id
        });
        if (hookHandled) return;

        const fields = this.getFieldsForQuery(this.currentEntity, 2); // Deeper nesting for details
        const query = `
            query Get${this.currentEntity.name}($id: String!) {
                ${this.currentEntity.name}(id: $id) {
                    ${fields}
                }
            }
        `;

        try {
            const data = await this.gqlQuery(query, { id });
            const item = data[this.currentEntity.name]; // NOSONAR
            let detailHtml = `<div class="back-controls">
                                <button id="back-to-list" class="btn btn-secondary">${this.t('back_to_list')}</button>
                              </div>
                <div class="table-container detail-view">
                    <table class="table">
                        <tbody>`;
            for (const key in this.currentEntity.columns) {
                const col = this.currentEntity.columns[key];
                const relationName = col.references;
                detailHtml += `<tr>
                                    <td>${this.getEntityLabel(this.currentEntity, key)}</td>
                                    <td>`;
                if (col.isForeignKey && item[relationName]) { // NOSONAR
                    let referenceValue = item[relationName][this.currentEntity.displayField];
                    if (referenceValue === null) referenceValue = 'N/A';
                    detailHtml += `<span>${referenceValue}</span>`;
                } else if ((col.type.includes('boolean') || key === this.currentEntity.activeField) && this.config.booleanDisplay) {
                    const isTrue = item[key] === 1 || item[key] === '1' || item[key] === true;
                    detailHtml += `<span>${isTrue ? this.t(this.config.booleanDisplay.trueLabelKey) : this.t(this.config.booleanDisplay.falseLabelKey)}</span>`;
                } else {
                    detailHtml += `<span>${item[key] !== null ? item[key] : 'N/A'}</span>`;
                }
                detailHtml += `</td></tr>`;
            }
            detailHtml += `</tbody></table></div>`;
            this.dom.tableDataContainer.innerHTML = detailHtml;
            document.getElementById('back-to-list').onclick = () => history.back();
        } catch (error) {
            this.dom.tableDataContainer.innerHTML = `<p style="color: red;">${this.t('failed_to_fetch_details')}</p>`;
        }
    }

    /**
     * Renders the add/edit form inside a modal. If an ID is provided, it fetches the item's data to pre-fill the form.
     * It can be overridden by a custom renderer hook.
     * @param {string|number|null} [id=null] - The ID of the item to edit. If null, an "add new" form is rendered.
     * @returns {Promise<void>} Resolves when the form is rendered and event handlers attached.
     */
    async renderForm(id = null) {
        this.dom.modalTitle.textContent = id ? this.t('edit_entity', this.currentEntity.displayName) : this.t('add_new_entity', this.currentEntity.displayName);
        this.dom.form.innerHTML = this.t('loading');
        this.openModal();

        // Panggil hook kustom untuk render form (insert/update)
        const action = id ? 'update' : 'insert';
        const hookHandled = this._invokeRenderHook(action, {
            entity: this.currentEntity,
            container: this.dom.form,
            id: id
        });
        if (hookHandled) return;

        let item = {};
        if (id) {
            const fields = this.getFieldsForQuery(this.currentEntity, 2); // Fetch with relations for edit form
            const query = `query GetForEdit($id: String!) { ${this.currentEntity.name}(id: $id) { ${fields} } }`;
            const data = await this.gqlQuery(query, { id }); // NOSONAR
            item = data[this.currentEntity.name];
        }

        let formHtml = '';
        for (const colName in this.currentEntity.columns) {
            if (colName === this.currentEntity.primaryKey) continue;
            const col = this.currentEntity.columns[colName];
            let value = '';
            if (col.isForeignKey && item[col.references]) {
                value = item[col.references][this.config.entities[this.camelCase(col.references)].primaryKey];
            } else if (item[colName] !== undefined) {
                value = item[colName];
            }

            formHtml += `<div class="form-group">`;
            formHtml += `<label for="${colName}">${this.getEntityLabel(this.currentEntity, colName)}</label>`;
            if (col.isForeignKey) {
                const relationName = col.references;
                const relatedEntity = this.config.entities[this.camelCase(relationName)];

                const relatedData = relatedEntity ? (await this.fetchAll(relatedEntity, { activeOnly: !id })) : [];
                const displayField = relatedEntity.displayField || 'name';

                formHtml += `<select id="${colName}" name="${colName}">`;
                formHtml += `<option value="">${this.t('select_option')}</option>`;
                relatedData.forEach(relItem => {
                    const relId = relItem[relatedEntity.primaryKey];
                    const relDisplay = relItem[displayField];
                    formHtml += `<option value="${relId}" ${relId == value ? 'selected' : ''}>${relDisplay}</option>`;
                });
                formHtml += `</select>`;
            } else {
                let activeField = this.currentEntity.activeField || this.defaultActiveField;
                let inputType = 'text';
                if (col.type.includes('int')) inputType = 'number';
                if (col.type.includes('boolean') || colName === activeField) inputType = 'checkbox';

                if(col.dataType.includes('datetime') || col.dataType.includes('timestamp')) {
                    inputType = 'datetime-local';
                } else if(col.dataType.includes('date')) {
                    inputType = 'date';
                }
                else if(col.dataType.includes('time')) {
                    inputType = 'time';
                }

                if (inputType === 'checkbox') {
                    formHtml += `<input type="checkbox" id="${colName}" name="${colName}" ${value ? 'checked' : ''}>`;
                } else {
                    formHtml += `<input type="${inputType}" id="${colName}" name="${colName}" value="${value}">`;
                }
            }
            formHtml += `</div>`;
        }

        this.dom.form.innerHTML = formHtml;

        this.dom.form.closest('.modal').querySelector('.modal-footer').innerHTML = `
        <button type="submit" class="btn btn-primary">${this.t('save')}</button> 
        <button type="button" class="btn btn-secondary" data-dismiss="modal">${this.t('close')}</button> `;

        this.dom.form.closest('.modal').querySelector('.modal-footer').querySelector('[type="submit"]').addEventListener('click', () => this.handleSave(id));

        this.dom.form.onsubmit = (e) => {
            e.preventDefault();
            this.handleSave(id);
        };
    }

    // =================================================================
    // HANDLER METHODS
    // =================================================================

    /**
     * Handles the save (create or update) operation when the form is submitted.
     * It constructs and sends the appropriate GraphQL mutation.
     * @param {string|number|null} id - The ID of the item being edited, or null if creating a new item.
     * @returns {Promise<void>} Resolves when the save operation completes (or throws on error).
     */
    async handleSave(id) {
        // Panggil hook kustom untuk proses save
        const action = id ? 'update' : 'insert';
        const hookHandled = this._invokeRenderHook(`save_${action}`, {
            entity: this.currentEntity,
            form: this.dom.form,
            id: id,
            app: this // Berikan akses ke instance aplikasi
        });
        if (hookHandled) return;

        let activeField = this.currentEntity.activeField || this.defaultActiveField;
        const formData = new FormData(this.dom.form);
        const input = {};
        for (const colName in this.currentEntity.columns) {
            if (colName === this.currentEntity.primaryKey) continue;
            const col = this.currentEntity.columns[colName];
            if (formData.has(colName)) {
                let value = formData.get(colName);

                // Handle checkbox boolean values
                if (this.dom.form.querySelector(`[name="${colName}"]`).type === 'checkbox') {
                    value = this.dom.form.querySelector(`[name="${colName}"]`).checked;
                }

                // Flexible type conversion based on frontend-config.json
                if (col.type.includes('boolean') || col.type.includes('int')) {
                    // Convert boolean to integer (1/0) for backend compatibility
                    value = value === true ? 1 : (value === false ? 0 : value);
                    // Convert string number to actual number
                    if (value !== '' && !isNaN(value)) value = Number(value);
                } else if (col.type.includes('float')) {
                    value = value ? Number(value) : null;
                }
                input[colName] = value;
            }
        }
        for (const colName in this.currentEntity.columns) {
            if (colName === this.currentEntity.primaryKey) continue;
            const col = this.currentEntity.columns[colName];

            // Handle unchecked checkboxes
            if (this.dom.form.querySelector(`[name="${colName}"]`).type === 'checkbox') {
                let value = this.dom.form.querySelector(`[name="${colName}"]`).checked;
                if (!value) {
                    if (col.type.includes('boolean')) {
                        input[colName] = false;
                    } else {
                        input[colName] = 0;
                    }
                }
            }
        }

        const methodName = this.ucFirst(this.currentEntity.name);
        const mutationName = id ? `update${methodName}` : `create${methodName}`;
        const fields = this.getFieldsForQuery(this.currentEntity, 1, true);

        // Helper to format values for inline GraphQL mutation
        const formatValue = (value) => {
            if (typeof value === 'string') {
                // Escape backslashes and double quotes
                const escapedValue = value.replace(/\\/g, '\\\\').replace(/"/g, '\\"');
                return `"${escapedValue}"`;
            }
            if (value === null || value === undefined) {
                return 'null';
            }
            return value; // For numbers and booleans
        };

        // Build the input object string for the mutation
        const inputString = Object.entries(input)
            .map(([key, value]) => `${key}: ${formatValue(value)}`)
            .join(', ');

        // Build the arguments string for the mutation
        let argsString = `input: {${inputString}}`;
        if (id) {
            argsString = `id: "${id}", ${argsString}`;
        }

        // Construct the final mutation string with inline values
        const mutation = `
            mutation {
                ${mutationName}(${argsString}) {
                    ${fields}
                }
            }
        `;

        try {
            await this.gqlQuery(mutation); // No variables needed
            this.closeModal();
            this.updateTableView(); // Refresh list
        } catch (error) {
            console.error(`Failed to save: ${error.message}`);
        }
    }

    /**
     * Handles the deletion of an item after user confirmation.
     * It shows a confirmation dialog before proceeding.
     * @param {string|number} id - The ID of the item to delete.
     * @returns {Promise<void>} Resolves when deletion completes.
     */
    async handleDelete(id) {
        const confirmed = await this.customConfirm({
            title: this.t('confirmation_title'),
            message: this.t('confirm_delete'),
            okText: this.t('delete'),
            cancelText: this.t('cancel')
        });
        if (!confirmed) return;

        const methodName = this.ucFirst(this.currentEntity.name);
        const mutation = `
            mutation {
                delete${methodName}(id: "${id}")
            }
        `;
        try {
            await this.gqlQuery(mutation); // No variables needed
            this.updateTableView();
            this.closeConfirmModal();
        } catch (error) {
            console.error(`Failed to delete: ${error.message}`);
        }
    }

    /**
     * Handles toggling the 'active' status of an item.
     * @param {string|number} id - The ID of the item to update.
     * @param {boolean} currentStatus - The current 'active' status of the item.
     * @returns {Promise<void>} Resolves when the toggle operation completes.
     */
    async handleToggleActive(id, currentStatus) {
        const newStatus = !currentStatus;
        const action = newStatus ? 'activate' : 'deactivate';

        const confirmed = await this.customConfirm({
            title: this.t('confirmation_title', ''),
            message: this.t('confirm_toggle_active', this.t(action)),
            okText: this.t('yes'),
            cancelText: this.t('no')
        });
        if (!confirmed) return;

        const methodName = this.ucFirst(this.currentEntity.name);

        const activeField = this.currentEntity.activeField || this.defaultActiveField;
        const mutation = `
            mutation Toggle${methodName}Active {
                toggle${methodName}Active(id: "${id}", ${activeField}: ${newStatus}) {${activeField}}
            }
        `;
        try {
            await this.gqlQuery(mutation); // No variables needed
            this.updateTableView();
            this.closeConfirmModal();
        } catch (error) {
            console.error(`Failed to ${action}: ${error.message}`);
        }
    }

    /**
     * Displays a custom confirmation modal and returns a promise that resolves with the user's choice.
     * This provides a consistent confirmation experience across the app.
     * @param {object} options - The options for the confirmation modal.
     * @param {string} [options.title] - The title of the modal.
     * @param {string} [options.message='Are you sure?'] - The message to display.
     * @param {string} [options.okText='OK'] - The text for the confirmation button.
     * @param {string} [options.cancelText='Cancel'] - The text for the cancellation button.
     * @returns {Promise<boolean>} A promise that resolves to true if OK is clicked, false otherwise.
     */
    customConfirm({ title = 'Confirmation', message = 'Are you sure?', okText = 'OK', cancelText = 'Cancel' }) {
        return new Promise(resolve => {
            const confirmModal = document.getElementById('customConfirmModal');
            const closeButton = confirmModal.querySelector('.modal-header .close-button');
            const closeButtons = confirmModal.querySelectorAll('[data-dismiss="modal"]');
            document.getElementById('customConfirmTitle').innerText = this.t(title);
            document.getElementById('customConfirmMessage').innerText = message;
            const okButton = document.getElementById('customConfirmOk');
            const cancelButton = document.getElementById('customConfirmCancel');
            okButton.innerText = this.t(okText);
            cancelButton.innerText = this.t(cancelText);

            const onOk = () => {
                cleanup();
                resolve(true);
            };
            const onCancel = () => {
                cleanup();
                resolve(false);
            };
            const cleanup = () => {
                okButton.removeEventListener('click', onOk);
                cancelButton.removeEventListener('click', onCancel);
                closeButton.removeEventListener('click', onCancel);
                closeButtons.forEach(btn => btn.removeEventListener('click', onCancel));
            };

            okButton.addEventListener('click', onOk, { once: true });
            cancelButton.addEventListener('click', onCancel, { once: true });
            closeButton.addEventListener('click', onCancel, { once: true });
            // The cancel button itself might have data-dismiss, so we handle all of them.
            // The { once: true } option is not ideal here if multiple buttons exist, so we'll manage listeners manually.
            closeButtons.forEach(btn => btn.addEventListener('click', onCancel));

            this.openConfirmModal();
        });
    }

    /**
     * Opens the confirmation modal.
     */
    openConfirmModal() {
        const confirmModal = document.getElementById('customConfirmModal');
        confirmModal.classList.add('show');
    }
    /**
     * Closes the confirmation modal.
     */
    closeConfirmModal() {
        const confirmModal = document.getElementById('customConfirmModal');
        confirmModal.classList.remove('show');
    }

    /**
     * Displays a custom alert/info modal.
     * This is a simple way to show information to the user.
     * @param {object} options - The options for the alert modal.
     * @param {string} [options.title='Info'] - The translation key for the modal title.
     * @param {string} [options.message] - The message to display.
     * @returns {Promise<void>} A promise that resolves when the modal is closed.
     */
    customAlert({ title = 'Info', message = '' }) {
        return new Promise(resolve => {
            this.dom.infoModalTitle.innerText = this.t(title);
            this.dom.infoModalMessage.innerText = message;

            // Translate the OK button
            this.dom.infoModalOk.innerText = this.t('ok');

            const closeButton = this.dom.infoModal.querySelector('.close-button');

            const cleanupAndResolve = () => {
                this.dom.infoModal.classList.remove('show');
                this.dom.infoModalOk.removeEventListener('click', cleanupAndResolve);
                closeButton.removeEventListener('click', cleanupAndResolve);
                resolve();
            };

            this.dom.infoModalOk.addEventListener('click', cleanupAndResolve, { once: true });
            closeButton.addEventListener('click', cleanupAndResolve, { once: true });

            this.openInfoModal();
        });
    }

    /**
     * Opens the info modal.
     */
    openInfoModal() {
        this.dom.infoModal.classList.add('show');
    }

    /**
     * Handles the login form submission.
     * On success, it reloads the page to establish a new session.
     * @param {Event} event - The form submission event.
     * @returns {Promise<void>} Resolves after handling the login response.
     */
    async handleLogin(event) {
        event.preventDefault();
        const formData = new FormData(this.dom.loginForm);
        const loginErrorDiv = document.getElementById('login-error');
        loginErrorDiv.textContent = '';

        const response = await fetch(this.loginUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            const result = await response.json();
            if (result.success) {
                // Jika login berhasil, tutup modal dan muat ulang halaman untuk mendapatkan sesi baru dan state aplikasi
                this.closeLoginModal();
                window.location.reload();
            } else {
                // Kasus yang seharusnya tidak terjadi jika server mengikuti logika yang ada
                loginErrorDiv.textContent = this.t('login_error');
            }
        } else if (response.status === 401) {
            // Jika login gagal (401 Unauthorized), tampilkan pesan error
            loginErrorDiv.textContent = this.t('invalid_credentials');
        } else {
            // Tangani error tak terduga lainnya
            loginErrorDiv.textContent = this.t('login_error');
            console.error('Login failed:', error);
            console.error('Login failed with status:', response.status);
        }
    }

    /**
     * Handles the logout process.
     * On success, it hides the main application and shows the login modal.
     * @param {Event} event - The click event from the logout button.
     * @returns {Promise<void>} Resolves after logout processing completes.
     */
    async handleLogout(event) {
        event.preventDefault();
        try {
            const response = await fetch(this.logoutUrl);
            if (response.ok) {
                // Hide the main page content and show the login modal
                this.dom.pageWrapper.style.display = 'none';
                this.openLoginModal();
            }
            else {
                await this.customAlert({
                    title: 'logout_failed_title',
                    message: this.t('logout_failed')
                });
            }
        } catch (error) {
            console.error('Logout failed:', error);
            await this.customAlert({
                title: 'logout_failed_title',
                message: this.t('logout_failed')
            });
        }
    }

    // =================================================================
    // UTILITY METHODS
    // =================================================================

    /**
     * Converts a snake_case string to camelCase.
     * @private
     * @param {string} str - The string to convert.
     * @returns {string} The camelCased string.
     */
    camelCase(str) {
        if(!str)
        {
            return '';
        }
        return str.replace(/_([a-z])/g, (g) => g[1].toUpperCase());
    }
    /**
     * Converts a camelCase string to snake_case.
     * @private
     * @param {string} str - The string to convert.
     * @returns {string} The snake_cased string.
     */
    snakeCase(str) {
        return str.replace(/([a-z0-9])([A-Z])/g, '$1_$2').toLowerCase();
    }
    /**
     * Converts a string to Title Case.
     * @private
     * @param {string} str - The string to convert.
     * @returns {string} The Title Cased string.
     */
    titleCase(str) {
        return str.replace(/\w\S*/g, (w) => w.charAt(0).toUpperCase() + w.substr(1).toLowerCase());
    }
    /**
     * Converts a snake_case string to Title Case.
     * @private
     * @param {string} str - The string to convert.
     * @returns {string} The Title Cased string.
     */
    snakeCaseToTitleCase(str) {
        return this.titleCase(str.replace(/_/g, ' '));
    }
    /**
     * Converts a camelCase string to Title Case.
     * @private
     * @param {string} str - The string to convert.
     * @returns {string} The Title Cased string.
     */
    camelCaseToTitleCase(str) {
        return this.titleCase(this.snakeCase(str).replace(/_/g, ' '));
    }

    /**
     * Capitalizes the first letter of a string.
     * @private
     * @param {string} str - The string to process.
     * @returns {string} The string with the first letter capitalized.
     */
    ucFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    /**
     * Recursively builds the fields string for a GraphQL query based on entity relationships.
     * @private
     * @param {object} entity - The entity configuration object.
     * @param {number} [depth=1] - The maximum depth for traversing relationships.
     * @param {boolean} [noRelations=false] - If true, only includes scalar fields.
     * @returns {string} A string of fields for the GraphQL query.
     */
    getFieldsForQuery(entity, depth = 1, noRelations = false) { // NOSONAR
        if (depth < 0) return entity.primaryKey;
        let fields = [];
        for (const colName in entity.columns) {
            const col = entity.columns[colName];
            if (col.isForeignKey && !noRelations) {
                // TODO:
                fields.push(colName);
                const relationNameCamelCase = col.references; 
                const relatedEntity = this.config.entities[this.camelCase(relationNameCamelCase)];

                if (relatedEntity) {
                    // Convert relation name to snake_case for the GraphQL query to match the PHP backend schema
                    const relationNameSnakeCase = relationNameCamelCase; // this.snakeCase(relationNameCamelCase); // e.g., "jenis_keanggotaan"
                    let query = `${relationNameSnakeCase} { ${this.getFieldsForQuery(relatedEntity, depth - 1)} }`;
                    // Use the correct relation name from the entity config for the query
                    fields.push(query);
                }
            } else if (!col.isForeignKey) {
                fields.push(colName);
            }
        }
        return fields.join(' ');
    }

    /**
     * Fetches all items for a given entity, typically for populating select dropdowns.
     * @private
     * @param {object} entity - The entity configuration object.
     * @param {object} [options={}] - Options for fetching data.
     * @param {boolean} [options.activeOnly=true] - If true, fetches only active items.
     * @returns {Promise<Array<object>>} A promise that resolves to an array of items.
     */
    async fetchAll(entity, options = {}) {
        const { activeOnly = true } = options;
        const fields = this.getFieldsForQuery(entity, 0); // Only simple fields

        const filterForQuery = [];
        if (activeOnly && entity.hasActiveColumn && entity.activeField) {
            const activeCol = entity.columns[entity.activeField];
            // The backend filter expects a string. For boolean-like integers, this is "1".
            const valueForFilter = (activeCol && activeCol.type.includes('boolean')) ? "1" : "1";

            filterForQuery.push({
                field: entity.activeField,
                value: valueForFilter,
                operator: "EQUALS"
            });
        }
        const query = `query FetchAll($limit: Int, $filter: [FilterInput]) { ${entity.pluralName}(limit: $limit, filter: $filter) { items { ${fields} } } }`;
        try {
            const data = await this.gqlQuery(query, { limit: 1000, filter: filterForQuery }); // Use a large limit to fetch all items
            return data[entity.pluralName].items;
        } catch (error) {
            console.error(`Failed to pre-fetch ${entity.pluralName}:`, error);
            return [];
        }
    }

    /**
     * Opens the main form modal.
     * @private
     * @returns {void}
     */
    openModal() { this.dom.modal.style.display = 'block'; }
    /**
     * Closes the main form modal and clears its content.
     * @private
     * @returns {void}
     */
    closeModal() { this.dom.modal.style.display = 'none'; this.dom.form.innerHTML = ''; }

    /**
     * Opens the login modal.
     * @private
     * @returns {void}
     */
    openLoginModal() { this.dom.loginModal.style.display = 'block'; }
    /**
     * Closes the login modal and resets the form.
     * @private
     * @returns {void}
     */
    closeLoginModal() { this.dom.loginModal.style.display = 'none'; this.dom.loginForm.reset(); document.getElementById('login-error').textContent = ''; }
}

/**
 * Generates a human-readable label from a snake_case or camelCase key.
 * This is a fallback for when a translation is not available.
 * @param {string} key - The key to convert.
 * @returns {string} The generated label.
 */
GraphQLClientApp.prototype._generateLabelFromKey = function (key) {
    return key.replace(/_/g, ' ').replace(/\w\S*/g, (w) => w.charAt(0).toUpperCase() + w.substr(1).toLowerCase());
};

