/**
 * Manages the frontend application for interacting with the GraphQL API.
 * Handles entity navigation, data rendering (lists, details, forms), and user interactions.
 */
class GraphQLClientApp {
    /**
     * Initializes the GraphQL client application.
     * This constructor sets up default configurations, merges them with user-provided options,
     * initializes application state, caches essential DOM elements, and triggers the main
     * initialization process for the single-page application.
     *
     * @param {object} [options={}] - Configuration options to override the defaults.
     * @param {string} [options.configUrl='frontend-config.php'] - URL to fetch the main application configuration.
     * @param {string} [options.apiUrl='graphql.php'] - URL of the GraphQL API endpoint.
     * @param {string} [options.loginUrl='login.php'] - URL for handling user login.
     * @param {string} [options.logoutUrl='logout.php'] - URL for handling user logout.
     * @param {string} [options.entityLanguageUrl='entity-language.php'] - URL to fetch entity-specific language translations.
     * @param {string} [options.i18nUrl='language.php'] - URL to fetch UI language packs.
     * @param {string} [options.languageConfigUrl='available-language.php'] - URL to fetch the list of available languages.
     * @param {string} [options.themeConfigUrl='available-theme.php'] - URL to fetch the list of available themes.
     * @param {string} [options.defaultThemeUrl='assets/style.min.css'] - Path to the default/fallback stylesheet.
     * @param {object} [options.customRenderers] - Custom rendering functions for entities (e.g., for list, detail, form views).
     * @param {string} [options.defaultActiveField='active'] - Default field name for the 'active' status column.
     * @param {string} [options.defaultDisplayField='name'] - Default field name to use for displaying relationships if not specified.
     * @param {?string} [options.languageId=null] - The initial language ID. If null, it will be auto-detected.
     * @param {string} [options.defaultLanguage='en'] - The fallback language if auto-detection fails.
     * @param {object} [options.pages] - An object to define custom, non-entity pages for the application.
     * @param {boolean} [options.useBrowserLanguage=true] - If true, attempts to use the browser's language if none is set in local storage.
     */
    constructor(options = {}) {
        const defaults = {
            configUrl: 'frontend-config.php',
            apiUrl: 'graphql.php',
            loginUrl: 'login.php',
            logoutUrl: 'logout.php',
            entityLanguageUrl: 'entity-language.php?lang={lang}',
            i18nUrl: 'language.php?lang={lang}',
            languageConfigUrl: 'available-language.php',
            themeConfigUrl: 'available-theme.php',
            defaultThemeUrl: 'assets/style.min.css',

            customRenderers: {},
            defaultActiveField: 'active',
            defaultDisplayField: 'name',

            languageId: null,

            defaultLanguage: 'en',
            useBrowserLanguage: true,
            maxMergedFilters: 0,
            pages: {},
        };

        Object.assign(this, defaults, options);

        this.supportedLanguages = {};
        /** @type {Array<object>} A list of available theme objects, e.g., [{name: 'green', title: 'Green'}]. */
        this.availableThemes = [];


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
         * @type {string} The current entity display name
         */
        this.currentEntityDisplayName = '';
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
            menuFilterInput: document.getElementById('entity-menu-filter'),
            title: document.getElementById('content-title'),
            body: document.getElementById('content-body'),
            modal: document.getElementById('form-modal'),
            modalTitle: document.getElementById('modal-title'),
            form: document.getElementById('entity-form'),
            closeModalBtn: document.querySelector('.close-button'),
            loginModal: document.getElementById('login-modal'),
            loginForm: document.getElementById('login-form'),
            logoutBtn: document.querySelector('.logout-link'),
            reloadConfigBtn: document.getElementById('reload-config-btn'),
            logoutBtnDropdown: document.getElementById('logout-btn-dropdown'),
            sidebarToggle: document.getElementById('sidebar-toggle'),
            sidebar: document.getElementById('sidebar-nav'),
            mainContent: document.getElementById('main-content'),
            langMenu: document.getElementById('lang-menu'),
            themeMenu: document.getElementById('theme-menu'),
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
            themeStylesheet: document.getElementById('theme-stylesheet'),
        };
        this.applicationTitle = document.querySelector('meta[name="title"]').getAttribute('content');
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

        // Reload config button handler
        if (this.dom.reloadConfigBtn) {
            this.dom.reloadConfigBtn.onclick = (e) => this.reloadConfiguration(e);
        }

        // Language selection handler
        this.dom.langMenu.addEventListener('click', (e) => {
            if (e.target.matches('a[data-lang]')) {
                e.preventDefault();
                const lang = e.target.dataset.lang;
                this.changeLanguage(lang);
            }
        });

        // Theme selection handler
        this.dom.themeMenu.addEventListener('click', (e) => {
            if (e.target.matches('a[data-theme-name]')) {
                e.preventDefault();
                const themeName = e.target.dataset.themeName;
                this.changeTheme(themeName);
                // Close dropdown after selection
                this.dom.themeMenu.classList.remove('active');
            }
        });

        // Theme toggle handler
        this.dom.themeToggle.addEventListener('click', () => this.toggleTheme());

        // Listen for theme changes in other tabs
        window.addEventListener('storage', (event) => {
            if (event.key === 'themeName') {
                this.applyTheme(event.newValue); // Apply color theme change
            } else if (event.key === 'colorMode') {
                this.applyThemeMode(event.newValue); // Apply dark/light mode change
            } else if (event.key === 'userLanguage') {
                this.changeLanguage(event.newValue); // Apply language change
            }
        });

        // Menu filter functionality
        if (this.dom.menuFilterInput) {
            this.dom.menuFilterInput.addEventListener('input', (e) => this.filterMenu(e.target.value));
        }

        // Add a click listener to the window to close modals when clicking outside
        window.addEventListener('click', (event) => {
            // Check if the clicked element is a modal backdrop
            if (event.target.classList.contains('modal')) {
                // Determine which modal was clicked and call its specific close function
                if (event.target.id === this.dom.modal.id) {
                    this.closeModal();
                } else if (event.target.id === this.dom.loginModal.id) {
                    // Do not close login modal
                } else if (event.target.id === 'customConfirmModal') {
                    this.closeConfirmModal();
                } else if (event.target.id === this.dom.infoModal.id) {
                    this.dom.infoModal.classList.remove('show');
                }
            }
        });

        // Add a keydown listener to the document to close the top-most modal on 'Escape' key press
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                // Find all visible modals
                const visibleModals = Array.from(document.querySelectorAll('.modal'))
                    .filter(modal => modal.style.display === 'block' || modal.classList.contains('show'))
                    .sort((a, b) => {
                        // Sort by z-index to find the top-most modal
                        const zIndexA = parseInt(window.getComputedStyle(a).zIndex, 10) || 0;
                        const zIndexB = parseInt(window.getComputedStyle(b).zIndex, 10) || 0;
                        return zIndexB - zIndexA;
                    });

                if (visibleModals.length > 0) {
                    // Trigger a click on the first modal's backdrop to close it
                    visibleModals[0].click();
                }
            }
        });

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
        this.hidePageWrapper();
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

        this.dom.loginForm.onsubmit = (e) => this.handleLogin(e);
        this.dom.logoutBtn.onclick = (e) => this.handleLogout(e);

        try {
            await this.initializeLanguage();
            await this.initializeTheme();
            await this.loadI18n();
            await this.loadLanguage();
            this.applyI18n();
            await this.loadConfig();
            this.showPageWrapper();
            this.buildMenu();
            this.initPage();
            
            window.onclick = (event) => {
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

            


            // Handle initial page load and back/forward button clicks
            window.addEventListener('popstate', () => this.handleRouteChange());
            this.handleRouteChange(); // Handle initial route
        } catch (error) {
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
        try { // NOSONAR
            const response = await fetch(this.languageConfigUrl, {
                headers: { 
                    'X-Requested-With': 'xmlhttprequest',
                    'X-Language-Id': this.languageId,
                    'Accept-Language': this.languageId, 
                }
            });
            if (!response.ok) throw new Error(`Could not fetch ${this.languageConfigUrl}`);
            const langConfig = await response.json();
            this.supportedLanguages = langConfig.supported;
            this.defaultLanguage = langConfig.default;

            const savedLang = localStorage.getItem('userLanguage');
            let langToLoad = this.defaultLanguage;


            if (savedLang && this.supportedLanguages[savedLang]) {
                // 1. Priority: Use language from local storage if it exists and is supported.
                langToLoad = savedLang;
            } else if (this.useBrowserLanguage) {
                // 2. If not in storage and option is enabled, try to use browser's language.
                const browserLang = navigator.language.split('-')[0];
                if (this.supportedLanguages[browserLang]) {
                    langToLoad = browserLang;
                }
            }

            // 3. Otherwise, it will remain as this.defaultLanguage.
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
     * Fetches available themes, validates the stored theme, and applies it.
     * @returns {Promise<void>}
     */
    async initializeTheme() {
        try {
            const response = await fetch(this.themeConfigUrl, {
                headers: { 
                    'X-Requested-With': 'xmlhttprequest',
                    'X-Language-Id': this.languageId,
                    'Accept-Language': this.languageId, 
                }
            });
            if (!response.ok) throw new Error(`Could not fetch ${this.themeConfigUrl}`);
            this.availableThemes = await response.json();
            // No need to apply here, it's handled by the script in index.php on initial load
            this.populateThemeMenu();

        } catch (error) {
            console.error("Failed to initialize theme configuration:", error);
            // Fallback to default stylesheet if config fails
            // No need to apply here
            this.populateThemeMenu(); // Populate with empty to avoid errors
        } finally {
            // Apply initial dark/light mode after theme is set
            const savedThemeMode = localStorage.getItem('colorMode') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            this.applyThemeMode(savedThemeMode);
        }
    }


    /**
     * Fetches and loads the main application configuration from the specified URL.
     * This configuration defines entities, columns, filters, etc.
     * @returns {Promise<void>} Resolves when configuration is loaded and applied.
     */
    async loadConfig() {
        const response = await fetch(this.configUrl, {
            headers: { 
                'X-Requested-With': 'xmlhttprequest',
                'X-Language-Id': this.languageId,
                'Accept-Language': this.languageId, 
            }
        });
        if (response.status === 401) {
            this.handleUnauthorized();
            throw new Error("Authentication required.");
        }
        if (!response.ok) throw new Error(`Failed to load config from ${this.configUrl}`); // NOSONAR
        this.config = await response.json();

        if (this.config.pagination) {
            if (this.config.pagination.pageSize) {
                this.state.limit = this.config.pagination.pageSize;
            }
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
                let url = this.entityLanguageUrl.replace('{lang}', this.languageId);
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'xmlhttprequest',
                        'Accept': 'application/json',
                        'X-Language-Id': this.languageId,
                        'Accept-Language': this.languageId,
                    }
                });
                if (response.status === 401) {
                    this.handleUnauthorized();
                    throw new Error("Authentication required.");
                }
                if (!response.ok) {
                    throw new Error(`Failed to load language from ${url}`); // NOSONAR
                }
                let data = await response.json();
                this.entityLanguagePack[this.languageId] = {};
                for (let name in data.entities) {
                    if(typeof this.entityLanguagePack[this.languageId][name] === 'undefined')
                    {
                        this.entityLanguagePack[this.languageId][name] = {};
                    }
                    this.entityLanguagePack[this.languageId][name].displayName = data.entities[name].displayName;
                    this.entityLanguagePack[this.languageId][name].columns = data.entities[name].columns;
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
                let url = this.i18nUrl.replace('{lang}', this.languageId);
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'xmlhttprequest',
                        'Accept': 'application/json',
                        'X-Language-Id': this.languageId,
                        'Accept-Language': this.languageId,
                    }
                });
                if (!response.ok) {
                    throw new Error(`Failed to load i18n from ${url}`); // NOSONAR
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
            // Special case for the menu filter placeholder
            if (el.id === 'entity-menu-filter' && el.hasAttribute('placeholder')) {
                el.placeholder = this.t('menu_filter');
                return;
            }
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
        const currentMode = document.documentElement.getAttribute('data-theme') || 'light';
        const newMode = currentMode === 'dark' ? 'light' : 'dark';
        this.applyThemeMode(newMode);
        localStorage.setItem('colorMode', newMode);
    }

    /**
     * Populates the theme dropdown menu.
     */
    populateThemeMenu() {
        this.dom.themeMenu.innerHTML = ''; // Clear existing items
        const currentTheme = localStorage.getItem('themeName');

        this.availableThemes.forEach(theme => {
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = '#';
            a.dataset.themeName = theme.name;
            if (theme.name === currentTheme) {
                a.classList.add('active');
            }
            a.textContent = theme.title;
            li.appendChild(a);
            this.dom.themeMenu.appendChild(li);
        });
    }

    /**
     * Changes the application's color theme.
     * @param {string} themeName - The name of the theme to apply.
     */
    changeTheme(themeName) {
        let currentTheme = localStorage.getItem('themeName');
        if (currentTheme === themeName) return;
        
        localStorage.setItem('themeName', themeName);
        this.applyTheme(themeName);

        // Update active class in the dropdown
        this.dom.themeMenu.querySelectorAll('a').forEach(a => {
            a.classList.toggle('active', a.dataset.themeName === themeName);
        });
    }

    /**
     * Applies a specific color theme by changing the stylesheet link.
     * This method ensures a smooth transition by preloading the new stylesheet
     * before removing the old one, preventing a "flash of unstyled content".
     * @param {?string} themeName - The name of the theme directory, or null to use the default.
     * @returns {Promise<void>}
     */
    async applyTheme(themeName) {
        const newUrl = themeName ? `assets/themes/${themeName}/style.min.css` : this.defaultThemeUrl;

        // If the new URL is the same as the current one, do nothing.
        if (this.dom.themeStylesheet && this.dom.themeStylesheet.href.endsWith(newUrl)) {
            return;
        }

        // Create a new link element for the new theme
        const newLink = document.createElement('link');
        newLink.rel = 'stylesheet';
        newLink.href = newUrl;

        // Append the new link to the head. The browser will start loading it.
        document.head.appendChild(newLink);

        // When the new stylesheet has loaded successfully
        newLink.onload = () => {
            // Get the old stylesheet element
            const oldLink = this.dom.themeStylesheet;

            // Remove the old stylesheet from the DOM
            if (oldLink && oldLink.parentNode) {
                oldLink.parentNode.removeChild(oldLink);
            }

            // Update the DOM cache to point to the new stylesheet and give it the primary ID
            this.dom.themeStylesheet = newLink;
            newLink.id = 'theme-stylesheet';
        };

        // Handle cases where the new stylesheet fails to load
        newLink.onerror = () => {
            console.error(`Failed to load theme: ${newUrl}. Keeping the current theme.`);
            // Remove the failed link element
            if (newLink.parentNode) {
                newLink.parentNode.removeChild(newLink);
            }
        };
    }

    /**
     * Applies a specific theme mode to the application ('light' or 'dark').
     * It sets a `data-theme` attribute on the `<html>` element.
     * @param {string} mode - The theme mode to apply ('light' or 'dark').
     */
    applyThemeMode(mode) {
        document.documentElement.setAttribute('data-theme', mode);
    }

    /**
     * Gets the translated label for a specific column (property) of an entity.
     * It searches for the translation in the loaded entity language pack using the current language.
     * If a translation is not found, it falls back to converting the column's key (e.g., 'user_name')
     * into a title-cased string (e.g., 'User Name').
     * @param {object} entity - The entity configuration object.
     * @param {string} key - The property key (column name) for which to find the label.
     * @returns {string} The translated column label or a generated title-cased label as a fallback.
     */
    getEntityLabel(entity, key) { // NOSONAR
        let entityName = this.snakeCase(entity.name);
        if (this.entityLanguagePack && this.entityLanguagePack[this.languageId]) {
            let entityLanguage = this.entityLanguagePack[this.languageId][entity.name];
            if (!entityLanguage) {
                entityLanguage = this.entityLanguagePack[this.languageId][entityName];
            }
            if (entityLanguage && entityLanguage.columns && entityLanguage.columns[key]) {
                return entityLanguage.columns[key];
            }
            return this.snakeCaseToTitleCase(key);
        } else {
            return this.snakeCaseToTitleCase(key);
        }
    }
    
    /**
     * Gets the translated display name for an entire entity.
     * It looks for the entity's `displayName` in the loaded language pack.
     * If not found, it falls back to converting the entity's camelCase name to Title Case.
     * @param {object} entity - The entity configuration object.
     * @returns {string} The translated entity display name or a generated name as a fallback.
     */
    getTranslatedEntityName(entity)
    {
        if (this.entityLanguagePack && this.entityLanguagePack[this.languageId]) {
            let entityLanguage = this.entityLanguagePack[this.languageId][entity.originalName];
            if (entityLanguage && entityLanguage.displayName) {
                return entityLanguage.displayName;
            }
            return this.camelCaseToTitleCase(entity.displayName);
        } else {
            return this.camelCaseToTitleCase(entity.displayName);
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
        let _this = this;
        if (!this.config || !this.config.entities) return;
        this.dom.menu.innerHTML = '';

        // Convert entities object to an array and sort it by the sortOrder property.
        // Entities without a sortOrder will be placed at the end.
        const sortedEntities = Object.values(this.config.entities).sort((a, b) => {
            const orderA = a.sortOrder !== undefined ? a.sortOrder : Infinity;
            const orderB = b.sortOrder !== undefined ? b.sortOrder : Infinity;
            return orderA - orderB;
        });

        sortedEntities.forEach((entity) => {
            if(entity.menu)
            {
                const li = document.createElement('li');
                const a = document.createElement('a');
                a.href = `#${entity.name}`;
                a.textContent = _this.getTranslatedEntityName(entity);
                // Reset filters and order when a menu item is clicked
                a.onclick = (e) => {
                    e.preventDefault();
                    this.navigateTo(entity.name, { limit: this.state.limit });
                };
                li.appendChild(a);
                this.dom.menu.appendChild(li);
            }
        });
    }

    /**
     * Filters the navigation menu items based on the provided text.
     * @param {string} filterText - The text to filter the menu by.
     */
    filterMenu(filterText) {
        const filter = filterText.toLowerCase().trim();
        const menuItems = this.dom.menu.querySelectorAll('li');

        menuItems.forEach(item => {
            const itemText = item.textContent.toLowerCase();
            if (itemText.includes(filter)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
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
                    'X-Language-Id': this.languageId,
                    'Accept-Language': this.languageId,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ query, variables }),
            });

            if (response.status === 401) {
                this.handleUnauthorized();
                throw new Error("Authentication required."); // NOSONAR
            }

            const result = await response.json();
            return result;
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
     * This method updates the browser history. It includes an optional `fromUrl`
     * parameter to facilitate a "back" navigation feature.
     * @param {string} entityName - The name of the entity.
     * @param {string|number} id - The ID of the item to display.
     * @param {?string} [fromUrl=null] - The URL to return to when navigating back from the detail view.
     * @returns {void}
     */
    navigateToDetail(entityName, id, fromUrl = null) {
        let fromParam = '';
        if (fromUrl) {
            fromParam = `?from=${encodeURIComponent(fromUrl)}`;
        }
        const newUrl = `${window.location.pathname}#${entityName}/detail/${id}${fromParam}`;
        history.pushState({ entityName, id }, '', newUrl);
        this.handleRouteChange();
    }

    /**
     * Handles the rendering of custom, non-entity pages (e.g., 'user-profile', 'settings').
     * These pages are defined in the `this.pages` object.
     * It can either fetch content from a URL or render predefined content.
     * @param {string} pageName - The key of the page to render, as defined in `this.pages`.
     * @returns {Promise<void>} A promise that resolves when the page has been handled.
     */
    async handlePage(pageName) {
        // Find the page configuration for the given name.
        const page = this.pages[pageName];
        if (!page) return;

        try {
            // Set the main content title and the browser document title.
            const title = this.t(page.title);
            this.dom.title.textContent = title;
            document.title = `${title} - ${this.applicationTitle}`;
            document.querySelectorAll('#entity-menu a').forEach(link => link.classList.remove('active'));

            // If the page is defined by a URL, fetch its content.
            if (page.url) {
                const [basePath, configQueryString] = page.url.split('?');
                const [hashPath, hashQueryString] = window.location.hash.split('?');

                const combinedParams = new URLSearchParams();
                new URLSearchParams(hashQueryString).forEach((value, key) => {
                    combinedParams.set(key, value);
                });

                const finalUrl = `${basePath}?${combinedParams.toString()}`;

                this.dom.loadingBar.style.display = 'block';
                // Prepare fetch parameters.
                let params = {
                    method: page.method,
                    headers: {
                        'X-Requested-With': 'xmlhttprequest',
                        'X-Language-Id': this.languageId,
                        'Accept-Language': this.languageId,
                        'Accept': page.accept || '*'
                    }
                };
                if (page.body) {
                    params.body = page.body;
                }
                // Make the request to the page's URL.
                const response = await fetch(finalUrl, params);

                if (response.status != 200) {
                    if (typeof page.error == 'function') {
                        page.error(response.status, response.statusText, this.dom.tableDataContainer, this.dom);
                    }
                    return;
                }

                let result;
                // Parse the response based on the expected 'accept' type.
                if (page?.accept && page.accept?.indexOf('json') != -1) {
                    result = await response.json();
                }
                else {
                    result = await response.text();
                }

                // Call the success callback with the fetched data.
                if (typeof page.success == 'function') {
                    page.success(result, this.dom.tableDataContainer, this.dom);
                }
            }
            // If the page is defined by static content, use its render function.
            else if (page.content && typeof page.render == 'function') {
                page.render(page.content, this.dom.tableDataContainer, this.dom);
            }

        } catch (error) {
            throw error;
        } finally {
            // Always hide the loading bar after the operation is complete.
            this.dom.loadingBar.style.display = 'none';
        }
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

        if (this.pages[pathParts[0]]) {
            this.handlePage(pathParts[0]);
            return;
        }

        const entityName = pathParts[0];
        const viewType = pathParts.length > 1 ? pathParts[1] : 'list';
        const itemId = pathParts.length > 2 ? pathParts[2] : null;

        const params = new URLSearchParams(queryString);

        const entity = this.config.entities[entityName];
        if (!entity) {
            this.dom.body.innerHTML = `<p>Page "${entityName}" not found.</p>`;
            return;
        }

        this.currentEntity = entity;
        let title = this.applicationTitle;
        
        this.currentEntityDisplayName = this.getTranslatedEntityName(entity);
        
        document.title = `${this.currentEntityDisplayName} - ${title}`;

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
        const pageTitle = this.t('dashboard');
        document.title = `${this.applicationTitle}`;
        this.dom.title.textContent = pageTitle;
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
        this.dom.title.textContent = this.t('list_of', this.currentEntityDisplayName);
        this.showPageWrapper();
        await this.renderFilters(); // Render filters and controls once
    }

    /**
     * Fetches data based on the current state (filters, pagination) and updates
     * the table and pagination sections of the view.
     * @returns {Promise<void>} Resolves when the view is updated.
     */
    async updateTableView() {
        const fields = this.getFieldsForQuery(this.currentEntity, 1, 1); // depth 1, maxDepth 1 (only fetch primary key and displaField if entity has displayField)
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
            query Get${this.ucFirst(this.currentEntity.pluralName)}($limit: Int, $offset: Int, $orderBy: [SortInput], $filter: [FilterInput]) {
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

            const queryResult = await this.gqlQuery(query, {
                limit: this.state.limit,
                offset: offset,
                orderBy: orderByForQuery,
                filter: filterForQuery,
            });
            let result = {};
            if(queryResult && queryResult.data)
            {
                const data = queryResult && queryResult.data ? queryResult.data : {};
                result = data && data[this.currentEntity.pluralName] ? data[this.currentEntity.pluralName] : {};
            }

            if(result && result.items && result.items.length > 0)
            {
                this.renderTable(result.items); // Renders into tableDataContainer
            }
            else
            {
                this.dom.tableDataContainer.innerHTML = `<p style="color: red;">Data not found</p>`;
            }
            this.renderPagination(result);
            if(result.errors && result.errors.length > 0)
            {
                console.error(result.errors);
            }
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
            return `<th class="${isSortable ? 'sortable' : ''}" data-sort-key="${h}" data-sort-direction="${isCurrentSort ? (this.state.orderBy.direction === 'ASC' ? 'asc' : 'desc') : ''}">
                                            ${this.getEntityLabel(this.currentEntity, h)}
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
                        const displayField = relatedEntity && relatedEntity.displayField ? relatedEntity.displayField : relatedEntity.primaryKey;
                        if(item[relationName] && typeof item[relationName][displayField] != 'undefined')
                        {
                            value = item[relationName][displayField] || 'N/A';
                        }
                        else if(item[relationName] && typeof item[relationName][relatedEntity.primaryKey] != 'undefined')
                        {
                            value = item[relationName][relatedEntity.primaryKey];
                        }
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

        document.querySelectorAll('.btn-detail').forEach(btn => btn.onclick = (e) => {
            // Allow default browser behavior for Ctrl+Click or Cmd+Click (open in new tab)
            if (e.ctrlKey || e.metaKey) {
                return;
            }
            // For normal clicks, prevent default and handle with SPA routing
            e.preventDefault();
            this.navigateToDetail(this.currentEntity.name, e.currentTarget.dataset.id, window.location.hash);
        });
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
     * Retrieves pre-fetched data for a specific entity from a data cache.
     * This is used by renderFiltersMerged to avoid making new network requests.
     * @param {object} entity - The entity configuration object for which to retrieve data.
     * @param {object} prefetchedData - The cache object containing data from the merged GraphQL query.
     * @returns {Array<object>} An array of items for the entity, or an empty array if not found.
     */
    getPrefetchedDataForEntity(entity, prefetchedData) {
        if (prefetchedData && prefetchedData[entity.pluralName] && prefetchedData[entity.pluralName].items) {
            return prefetchedData[entity.pluralName].items;
        }
        return [];
    }

    /**
     * Acts as a router for rendering filter controls.
     * It decides whether to fetch filter data in a single merged query (`renderFiltersMerged`)
     * or through separate queries for each filter (`renderFiltersSeparated`). The choice is based on
     * comparing the `filterEntities` count in the entity's configuration against `maxMergedFilters`.
     * This optimizes network requests by using a merged query for entities with fewer filter dependencies.
     * @returns {Promise<void>} A promise that resolves when the appropriate filter rendering method has completed.
     */
    async renderFilters()
    {
        return this.currentEntity.filterEntities && this.currentEntity.filterEntities > this.maxMergedFilters ? await this.renderFiltersSeparated() : await this.renderFiltersMerged();
    }

    /**
     * Renders filter controls by pre-fetching data for all select elements in a single merged GraphQL query.
     * This is an optimized version of `renderFilters` that reduces network requests. It first identifies
     * all filters that require data from related entities, constructs a single GraphQL query to fetch them all,
     * and then builds the HTML for the filters using the pre-fetched data.
     *
     * @returns {Promise<void>} A promise that resolves when the filters have been rendered and event listeners are attached.
     */
    async renderFiltersMerged() {
        const hasFilters = this.currentEntity.filters && this.currentEntity.filters.length > 0;
        let filterHtml = '';
        let prefetchedData = {};

        if (hasFilters) {
            // Phase 1: Aggregate and pre-fetch all data for select filters
            const queriesToMerge = [];
            const selectFilters = this.currentEntity.filters.filter(f => f.element === 'select');

            for (const filter of selectFilters) {
                const col = this.currentEntity.columns[filter.name];
                if (col && col.isForeignKey) {
                    const relatedEntity = this.config.entities[this.camelCase(col.references)];
                    if (relatedEntity) {
                        const fields = this.getFieldsForQuery(relatedEntity, 0, 0);
                        // Add each entity query to the list, using its pluralName as the key
                        queriesToMerge.push(`${relatedEntity.pluralName}(limit: 1000) { items { ${fields} } }`);
                    }
                }
            }

            if (queriesToMerge.length > 0) {
                const mergedQuery = `query FetchAllFilterData { ${queriesToMerge.join('\n')} }`;
                try {
                    const result = await this.gqlQuery(mergedQuery);
                    prefetchedData = result.data;
                } catch (error) {
                    console.error("Failed to pre-fetch filter data:", error);
                    // Continue with empty data, dropdowns will be empty
                }
            }

            // Phase 2: Build the HTML using the pre-fetched data
            filterHtml += '<div class="filter-controls">';
            for (const filter of this.currentEntity.filters) {
                const currentValue = this.state.filters[filter.name] || '';
                filterHtml += `<div class="form-group">`;
                filterHtml += `<label for="filter-${filter.name}">${this.getEntityLabel(this.currentEntity, filter.name)}</label>`;

                if (filter.element === 'select') {
                    const col = this.currentEntity.columns[filter.name];
                    const relatedEntity = this.config.entities[this.camelCase(col.references)];
                    const relatedData = this.getPrefetchedDataForEntity(relatedEntity, prefetchedData); // Use the new helper
                    const displayField = relatedEntity.displayField || relatedEntity.primaryKey;

                    filterHtml += `<select id="filter-${filter.name}" name="${filter.name}">`;
                    filterHtml += `<option value="">${this.t('select_option')}</option>`;
                    relatedData.forEach(relItem => {
                        const relId = relItem[relatedEntity.primaryKey];
                        const relDisplay = relItem[displayField] || relId;
                        filterHtml += `<option value="${relId}" ${String(relId) === String(currentValue) ? 'selected' : ''}>${relDisplay}</option>`;
                    });
                    filterHtml += `</select>`;
                } else {
                    filterHtml += `<input type="text" id="filter-${filter.name}" name="${filter.name}" value="${currentValue}" placeholder="${this.getEntityLabel(this.currentEntity, filter.name)}">`;
                }
                filterHtml += `</div>`;
            }
            filterHtml += `<button id="search-btn" class="btn btn-primary">${this.t('search')}</button>`;
            filterHtml += `<button id="reset-filter-btn" class="btn btn-secondary">${this.t('reset_filter')}</button>`;
        }

        filterHtml += `<button id="add-new-btn" class="btn btn-primary">${this.t('add_new', this.currentEntityDisplayName)}</button>`;

        if (hasFilters) {
            filterHtml += '</div>';
        }

        this.dom.filterContainer.innerHTML = filterHtml;

        if (hasFilters) {
            document.getElementById('search-btn').onclick = () => this.handleSearch();
            document.getElementById('reset-filter-btn').onclick = () => this.handleResetFilters();
            this.dom.filterContainer.querySelectorAll('.filter-controls input, .filter-controls select').forEach(input => {
                input.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        this.handleSearch();
                    }
                });
            });
        }
        document.getElementById('add-new-btn').onclick = () => this.renderForm();
    }

    /**
     * Renders filter controls and the "Add New" button based on the current entity's configuration.
     * Attaches event listeners for the search button and for the 'Enter' key on filter inputs.
     * For select filters, it pre-fetches data for the dropdown options.
     * @returns {Promise<void>}
     */
    async renderFiltersSeparated() {
        const hasFilters = this.currentEntity.filters && this.currentEntity.filters.length > 0; // NOSONAR
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
                    const displayField = relatedEntity.displayField || relatedEntity.primaryKey;

                    filterHtml += `<select id="filter-${filter.name}" name="${filter.name}">`;
                    filterHtml += `<option value="">${this.t('select_option')}</option>`;
                    relatedData.forEach(relItem => {
                        const relId = relItem[relatedEntity.primaryKey];
                        const relDisplay = relItem[displayField] || relId;
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
        filterHtml += `<button id="add-new-btn" class="btn btn-primary">${this.t('add_new', this.currentEntityDisplayName)}</button>`;

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
        this.dom.paginationContainer.style.display = 'none';
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
        const entityName = this.currentEntity.name;
        const id = item[this.currentEntity.primaryKey]; // NOSONAR
        
        // Encode the current list view URL to be passed as a parameter
        const fromUrl = encodeURIComponent(window.location.hash);
        let detailLink = `${window.location.pathname}#${entityName}/detail/${id}?from=${fromUrl}`;

        let buttons = `<td class="actions">`;
        buttons += `<a class="btn btn-sm btn-info btn-detail" data-id="${id}" href="${detailLink}" data-from-url="${window.location.hash}">${this.t('view')}</a> `;
        buttons += `<button class="btn btn-sm btn-primary btn-edit" data-id="${id}">${this.t('edit')}</button> `;

        if (this.currentEntity.hasActiveColumn) {
            const activeField = this.currentEntity.activeField || this.defaultActiveField;

            const isActive = item[activeField] === 1 || item[activeField] === '1' || item[activeField] === true;

            buttons += `<button class="btn btn-sm ${isActive ? 'btn-warning' : 'btn-success'} btn-toggle-active" data-id="${id}" data-active="${isActive}">${isActive ? this.t('deactivate') : this.t('activate')}</button> `;
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
            const currentHash = window.location.hash;
            const [entityPart, queryPart] = currentHash.split('?');

            const existingParams = new URLSearchParams(queryPart || '');

            existingParams.set('page', pageNumber);

            existingParams.set('limit', this.state.limit);

            Object.entries(this.state.filters).forEach(([key, value]) => {
                if (value) {
                    existingParams.set(key, value);
                } else {
                    existingParams.delete(key); 
                }
            });

            if (this.state.orderBy.field) {
                existingParams.set('orderBy', this.state.orderBy.field);
                existingParams.set('orderDir', this.state.orderBy.direction);
            } else {
                existingParams.delete('orderBy');
                existingParams.delete('orderDir');
            }

            const entityName = this.currentEntity.name;

            return `${window.location.pathname}#${entityName}?${existingParams.toString()}`;
        };


        const prevUrl = result.hasPrevious ? buildPageUrl(prevPage) : '#';
        const nextUrl = result.hasNext ? buildPageUrl(nextPage) : '#';

        this.dom.paginationContainer.innerHTML = `
            <span>${this.t('page_of', result.page, result.totalPages, result.total)}</span>
            <a id="prev-page" href="${prevUrl}" class="btn btn-secondary ${!result.hasPrevious ? 'disabled' : ''}">${this.t('previous')}</a>
            <a id="next-page" href="${nextUrl}" class="btn btn-secondary ${!result.hasNext ? 'disabled' : ''}">${this.t('next')}</a>
        `;
        this.dom.paginationContainer.style.display = 'flex';

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
     * Makes the main application content wrapper visible.
     * This is typically called after the initial loading and authentication are complete,
     * revealing the main user interface.
     * @returns {void}
     */
    showPageWrapper()
    {
        this.dom.pageWrapper.style.display = 'block';
    }

    /**
     * Hides the main application content wrapper.
     * This is used to conceal the main UI, for instance, during initial loading
     * or when a full-screen modal like the login screen is displayed.
     * @returns {void}
     */
    hidePageWrapper()
    {
        this.dom.pageWrapper.style.display = 'none';
    }

    /**
     * Fetches and renders the detail view for a single item.
     * It can be overridden by a custom renderer hook.
     * @param {string|number} id - The ID of the item to display.
     * @returns {Promise<void>} Resolves when the detail view has been rendered.
     */
    async renderDetailView(id) {
        this.dom.title.textContent = this.t('detail_of', this.currentEntityDisplayName); // NOSONAR

        // Call a custom hook for detail rendering
        const hookHandled = this._invokeRenderHook('detail', {
            entity: this.currentEntity,
            container: this.dom.body,
            id: id
        });
        if (hookHandled) return;

        const fields = this.getFieldsForQuery(this.currentEntity, 2, 2); // Deeper nesting for details
        const query = `
            query Get${this.ucFirst(this.currentEntity.name)}($id: String!) {
                ${this.currentEntity.name}(id: $id) {
                    ${fields}
                }
            }
        `;

        try {
            const queryResult = await this.gqlQuery(query, { id });
            const data = queryResult.data;
            this.showPageWrapper();
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
            detailHtml += `</tbody></table></div>`; // NOSONAR
            this.dom.tableDataContainer.innerHTML = detailHtml;

            document.getElementById('back-to-list').onclick = () => {
                // Priority 1: Use history.back() if it's a safe intra-app navigation.
                // This is the most efficient way to return to the previous state.
                if (history.length > 1 && document.referrer && new URL(document.referrer).origin === window.location.origin) {
                    history.back();
                    return;
                }

                // Priority 2: Use the 'from' parameter if available.
                // This is useful when the detail page is opened in a new tab or from a shared link.
                const params = new URLSearchParams(window.location.hash.split('?')[1]);
                const fromUrl = params.get('from');
                if (fromUrl) {
                    window.location.hash = decodeURIComponent(fromUrl);
                } else {
                    // Priority 3 (Fallback): Navigate to the default list view for the entity.
                    this.navigateTo(this.currentEntity.name);
                }
            };
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
        this.dom.modalTitle.textContent = id ? this.t('edit_entity', this.currentEntityDisplayName) : this.t('add_new_entity', this.currentEntityDisplayName);
        this.dom.form.innerHTML = this.t('loading');
        this.openModal();

        // Call a custom hook for form rendering (insert/update)
        const action = id ? 'update' : 'insert';
        const hookHandled = this._invokeRenderHook(action, {
            entity: this.currentEntity,
            container: this.dom.form,
            id: id
        });
        if (hookHandled) return;

        let item = {};
        if (id) {
            const fields = this.getFieldsForQuery(this.currentEntity, 2, 2); // Fetch with relations for edit form
            const query = `query GetForEdit($id: String!) { ${this.currentEntity.name}(id: $id) { ${fields} } }`;
            const queryResult = await this.gqlQuery(query, { id }); // NOSONAR
            const data = queryResult.data;
            item = data[this.currentEntity.name];
        }

        let formHtml = '';
        for (const colName in this.currentEntity.columns) {
            // Exclude primary key
            
            if (colName === this.currentEntity.primaryKey && 
                (
                    (action === 'insert' && this.currentEntity.columns[colName].primaryKeyValue === 'autogenerated') ||
                    (action === 'update' && this.currentEntity.columns[colName].primaryKeyValue != 'manual-all')
                )
            )
            {
                continue;
            }

            // Exclude backend handled columns
            if (this.currentEntity.backendHandledColumns && this.currentEntity.backendHandledColumns.includes(colName)) {
                continue;
            }

            const col = this.currentEntity.columns[colName];
            let value = '';
            if (col.isForeignKey && item[col.references]) {
                value = item[col.references][this.config.entities[this.camelCase(col.references)].primaryKey];
            } else if (item[colName] !== undefined) {
                value = item[colName];
            }

            
            let formElement = '';
            let formElementType = 'text';
            formElement += `<label for="${colName}">${this.getEntityLabel(this.currentEntity, colName)}</label>`;
            if (col.isForeignKey) {
                const relationName = col.references;
                const relatedEntity = this.config.entities[this.camelCase(relationName)];

                const relatedData = relatedEntity ? (await this.fetchAll(relatedEntity, { activeOnly: !id })) : [];
                const displayField = relatedEntity.displayField || relatedEntity.primaryKey;

                formElement += `<select id="${colName}" name="${colName}">`;
                formElement += `<option value="">${this.t('select_option')}</option>`;
                relatedData.forEach(relItem => {
                    const relId = relItem[relatedEntity.primaryKey];
                    const relDisplay = relItem[displayField] || relId;
                    formElement += `<option value="${relId}" ${relId == value ? 'selected' : ''}>${relDisplay}</option>`;
                });
                formElement += `</select>`;
                formElementType = 'select';
            } else if (col.element === 'textarea') {
                formElement += `<textarea id="${colName}" name="${colName}" spellcheck="false" autocomplete="off">${value}</textarea>`;
                formElementType = 'textarea';
            } else {
                let activeField = this.currentEntity.activeField || this.defaultActiveField;
                let inputType = 'text';

                // From GraphQL type
                if (col.type.includes('int')) inputType = 'number';
                if (col.type.includes('boolean') || colName === activeField) inputType = 'checkbox';

                // From dataType
                if (col.dataType.includes('datetime') || col.dataType.includes('timestamp')) {
                    inputType = 'datetime-local';
                    formElementType = 'text';
                } else if (col.dataType.includes('date')) {
                    inputType = 'date';
                    formElementType = 'text';
                }
                else if (col.dataType.includes('time')) {
                    inputType = 'time';
                    formElementType = 'text';
                }
                else if (col.dataType.includes('float')) {
                    // Two attributes
                    inputType = 'number" step="any';
                    formElementType = 'text';
                }

                if (inputType === 'checkbox') {
                    formElement += `<input type="checkbox" id="${colName}" name="${colName}" ${value ? 'checked' : ''}>`;
                    formElementType = 'checkbox';
                } else {
                    formElement += `<input type="${inputType}" id="${colName}" name="${colName}" value="${value}" autocomplete="off">`;
                    formElementType = 'text';
                }
                
            }
            formHtml += `<div class="form-group form-element-${formElementType}">${formElement}</div>`;
        }

        // Add a placeholder for form-level error messages
        formHtml += `<div id="form-error"></div>`;

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
        // Call a custom hook for the save process.
        const action = id ? 'update' : 'insert';
        const hookHandled = this._invokeRenderHook(`save_${action}`, {
            entity: this.currentEntity,
            form: this.dom.form,
            id: id,
            app: this // Provide access to the application instance
        });
        if (hookHandled) return;

        // Find and clear any previous error messages before attempting to save
        const errorDiv = this.dom.form.querySelector('#form-error');
        if (errorDiv) {
            errorDiv.textContent = '';
            errorDiv.classList.remove('show');
        }

        let activeField = this.currentEntity.activeField || this.defaultActiveField;
        const formData = new FormData(this.dom.form);
        const input = {};
        for (const colName in this.currentEntity.columns) {
            if (colName === this.currentEntity.primaryKey && this.currentEntity.columns[colName].primaryKeyValue === 'autogenerated') 
            {
                continue;
            } // NOSONAR
            if (action == 'update' && colName === this.currentEntity.primaryKey && this.currentEntity.columns[colName].primaryKeyValue === 'manual-insert') 
            {
                // User cannot update this value.
                input[colName] = id;
                continue; // NOSONAR
            }

            const col = this.currentEntity.columns[colName];
            if (formData.has(colName)) {
                let value = formData.get(colName);

                // Handle checkbox boolean values
                if (this.dom.form.querySelector(`[name="${colName}"]`).type === 'checkbox') {
                    value = this.dom.form.querySelector(`[name="${colName}"]`).checked;
                }

                // Flexible type conversion based on frontend-config.json
                if(col.type.includes('boolean'))
                {
                    value = value === 'true' || value === '1' || value === 1 || value === true;
                } else if (col.type.includes('int')) {
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
            if (this.dom.form.querySelector(`[name="${colName}"]`) && this.dom.form.querySelector(`[name="${colName}"]`).type === 'checkbox') {
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
        const fields = this.getFieldsForQuery(this.currentEntity, 1, 1, true);

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
            let queryResult = await this.gqlQuery(mutation); // No variables needed
            if(queryResult.errors) throw new Error(queryResult.errors[0].message);
            this.closeModal();
            this.updateTableView(); // Refresh list
        } catch (error) {
            console.error(`Failed to save: ${error.message}`);
            this.dom.form.querySelector('#form-error').textContent = error.message;
            this.dom.form.querySelector('#form-error').style.display = 'block';
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
            const queryResult = await this.gqlQuery(mutation); // No variables needed
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
            const queryResult = await this.gqlQuery(mutation); // No variables needed
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
     * @param {?number} [options.timeout=null] - Optional. Time in milliseconds to auto-close the alert.
     * @returns {Promise<void>} A promise that resolves when the modal is closed.
     */
    customAlert({ title = 'Info', message = '', timeout = null }) {
        return new Promise(resolve => {
            this.dom.infoModalTitle.innerText = this.t(title);
            this.dom.infoModalMessage.innerText = message;

            // Translate the OK button
            this.dom.infoModalOk.innerText = this.t('ok');

            const closeButton = this.dom.infoModal.querySelector('.close-button');
            let timeoutId = null;

            const cleanupAndResolve = () => {
                // If a timeout is set, clear it to prevent it from running after manual close.
                if (timeoutId) {
                    clearTimeout(timeoutId);
                }
                this.dom.infoModal.classList.remove('show');
                this.dom.infoModalOk.removeEventListener('click', cleanupAndResolve);
                closeButton.removeEventListener('click', cleanupAndResolve);
                resolve();
            };

            this.dom.infoModalOk.addEventListener('click', cleanupAndResolve, { once: true });
            closeButton.addEventListener('click', cleanupAndResolve, { once: true });

            // If a timeout is provided and is a valid number, set it.
            if (typeof timeout === 'number' && timeout > 0) {
                timeoutId = setTimeout(cleanupAndResolve, timeout);
            }

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
                'X-Requested-With': 'xmlhttprequest',
                'X-Language-Id': this.languageId,
                'Accept-Language': this.languageId,
                'Accept': 'application/json'
            },
        });

        if (response.ok) {
            const result = await response.json();
            if (result.success) {
                // If login is successful, close the modal and reload the page to get a new session and application state.
                this.closeLoginModal();
                window.location.reload();
            } else {
                // This case should not happen if the server follows the expected logic.
                loginErrorDiv.textContent = this.t('login_error');
            }
        } else if (response.status === 401) { // NOSONAR
            // If login fails (401 Unauthorized), display an error message.
            loginErrorDiv.textContent = this.t('invalid_credentials');
        } else {
            // Handle other unexpected errors.
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
            const response = await fetch(this.logoutUrl, {
                headers: { 'X-Requested-With': 'xmlhttprequest' }
            });
            if (response.ok) {
                // Hide the main page content and show the login modal
                this.hidePageWrapper();
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

    /**
     * Reloads the application's configuration files without a full page refresh.
     * This re-fetches frontend config, UI translations, and entity translations,
     * then rebuilds the UI to reflect any changes.
     * @param {Event} event - The click event from the reload button.
     * @returns {Promise<void>}
     */
    async reloadConfiguration(event) {
        if(event)
        {
            event.preventDefault();
        }
        this.dom.loadingBar.style.display = 'block';
        try {
            await this.loadConfig();
            await this.loadI18n();
            await this.loadLanguage();
            this.buildMenu();
            this.applyI18n();
            await this.handleRouteChange(); // Re-render the current view
            await this.customAlert({ title: 'success', message: this.t('app_refreshed_successfully'), timeout: 2000});
        } catch (error) {
            console.error('Failed to reload configuration:', error);
            await this.customAlert({ title: 'error', message: this.t('app_refresh_failed')});
        } finally {
            this.dom.loadingBar.style.display = 'none';
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
        if (!str) {
            return '';
        }
        return str.replace(/_([a-z])/g, (g) => g[1].toUpperCase());
    }
    
    /**
     * Converts a string to UpperCamelCase.
     * @private
     * @param {string} str - The string to convert.
     * @returns {string} The UpperCamelCased string.
     */
    upperCamelCase(str) {
        const camel = this.camelCase(str);
        return camel.charAt(0).toUpperCase() + camel.slice(1);
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
     * @param {number} [maxDepth=1] - The maximum depth from initiator.
     * @param {boolean} [noRelations=false] - If true, only includes scalar fields.
     * @returns {string} A string of fields for the GraphQL query.
     */
    getFieldsForQuery(entity, depth = 1, maxDepth = 1, noRelations = false) { // NOSONAR
        if (depth < 0) return entity.primaryKey;
        let fields = [];
        for (const colName in entity.columns) {
            const col = entity.columns[colName];

            if(maxDepth == 1 && depth < 1)
            {
                if(col.isPrimaryKey || colName == entity.displayField)
                {
                    fields.push(colName);
                }
            }
            else
            {
                if (col.isForeignKey && !noRelations) {
                    fields.push(colName);
                    const relationNameCamelCase = col.references;
                    const relatedEntity = this.config.entities[this.camelCase(relationNameCamelCase)];

                    if (relatedEntity) {
                        // Convert relation name to snake_case for the GraphQL query to match the PHP backend schema
                        const relationNameSnakeCase = relationNameCamelCase; // this.snakeCase(relationNameCamelCase); // e.g., "jenis_keanggotaan"
                        let query = `${relationNameSnakeCase} { ${this.getFieldsForQuery(relatedEntity, depth - 1, maxDepth)} }`;
                        // Use the correct relation name from the entity config for the query
                        fields.push(query);
                    }
                } else if (!col.isForeignKey) {
                    fields.push(colName);
                }
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
        const fields = this.getFieldsForQuery(entity, 0, 0); // Only simple fields

        const filterForQuery = [];
        if (activeOnly && entity.hasActiveColumn && entity.activeField) {
            const activeCol = entity.columns[entity.activeField];
            // The backend filter with ObjectScalar now correctly handles boolean types.
            const valueForFilter = true;

            filterForQuery.push({
                field: entity.activeField,
                value: valueForFilter,
                operator: "EQUALS"
            });
        }
        const query = `query FetchAll($limit: Int, $filter: [FilterInput]) { ${entity.pluralName}(limit: $limit, filter: $filter) { items { ${fields} } } }`;
        try {
            const queryResult = await this.gqlQuery(query, { limit: 1000, filter: filterForQuery }); // Use a large limit to fetch all items
            const data = queryResult.data;
            if(typeof data != 'undefined' && data[entity.pluralName] && data[entity.pluralName].items)
            {
                return data[entity.pluralName].items;
            }
            else
            {
                return [];
            }
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
