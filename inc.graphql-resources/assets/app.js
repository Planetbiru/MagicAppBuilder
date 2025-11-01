// Global variable to hold the application instance.
let graphqlApp = null;

// Wait for the DOM to be fully loaded before initializing the application.
document.addEventListener('DOMContentLoaded', () => {
    /**
     * Create a single instance of the GraphQLClientApp.
     * The constructor handles the entire initialization process, including fetching
     * configurations, setting up routing, and attaching event listeners.
     */
    graphqlApp = new GraphQLClientApp({

        // --- Configuration for backend communication ---

        // URL to fetch the main frontend configuration (entities, columns, etc.).
        configUrl: 'frontend-config.php',
        // The endpoint for all GraphQL queries and mutations.
        apiUrl: 'graphql.php',
        // URL to handle user login requests.
        loginUrl: 'login.php',
        // URL to handle user logout requests.
        logoutUrl: 'logout.php',
        // URL to fetch language translations for entity and column names.
        entityLanguageUrl: 'entity-language.php',
        // URL to fetch general UI translations (i18n).
        i18nUrl: 'language.php',
        // URL to get the list of available languages.
        languageConfigUrl: 'available-language.php',

        // --- Default field names ---

        // The default field name used for 'active' status toggles if not specified by the entity.
        defaultActiveField: 'active',
        // The default field name to display for a related entity (e.g., in dropdowns) if not specified.
        defaultDisplayField: 'name',

        // --- Language and Internationalization (i18n) ---

        // The initial language ID. If null, it will be auto-detected from browser/local storage.
        languageId: null,
        // The fallback language to use if detection fails or the language is not supported.
        defaultLanguage: 'en',

        // --- Customization Hooks ---

        // An object to define custom rendering functions for specific entities or views.
        customRenderers: {},

    });


    graphqlApp.pages['user-profile'] = {
        url: 'user-profile.php',
        title: 'profile', // The translation key for the page title.
        method: 'GET',
        headers: {
            'X-Language-Id': graphqlApp.languageId,
            'Accept-Language': graphqlApp.languageId
        },
        accept: 'text/html',
        // Callback function executed on a successful fetch.
        success: (data, container, dom) => {
            // Hide standard entity view elements.
            dom.filterContainer.style.display = 'none';
            dom.paginationContainer.style.display = 'none';
            dom.filterContainer.innerHTML = '';
            dom.tableDataContainer.innerHTML = '';
            // Inject the fetched HTML into the main content container.
            container.innerHTML = data;
        },
        // Callback function for handling errors.
        error: (errorCode, errorMessage, container, dom) => {
            console.error(error);
        },
        // A general render function (can be used for static content).
        render: (data, container, dom) => {
            // Not used here as content is fetched via URL.
        }
    };
    graphqlApp.pages['user-profile-update'] = {
        url: 'user-profile-update.php',
        title: 'profile', // The translation key for the page title.
        method: 'GET',
        headers: {
            'X-Language-Id': graphqlApp.languageId,
            'Accept-Language': graphqlApp.languageId
        },
        accept: 'text/html',
        // Callback function executed on a successful fetch.
        success: (data, container, dom) => {
            // Hide standard entity view elements.
            dom.filterContainer.style.display = 'none';
            dom.paginationContainer.style.display = 'none';
            dom.filterContainer.innerHTML = '';
            dom.tableDataContainer.innerHTML = '';
            // Inject the fetched HTML into the main content container.
            container.innerHTML = data;
        },
        // Callback function for handling errors.
        error: (errorCode, errorMessage, container, dom) => {
            console.error(error);
        },
        // A general render function (can be used for static content).
        render: (data, container, dom) => {
            // Not used here as content is fetched via URL.
        }
    };
    graphqlApp.pages['settings'] = {
        url: 'settings.php',
        title: 'settings', // The translation key for the page title.
        method: 'GET',
        headers: {
            'X-Language-Id': graphqlApp.languageId,
            'Accept-Language': graphqlApp.languageId
        },
        accept: 'text/html',
        // Callback function executed on a successful fetch.
        success: (data, container, dom) => {
            // Hide standard entity view elements.
            dom.filterContainer.style.display = 'none';
            dom.paginationContainer.style.display = 'none';
            dom.filterContainer.innerHTML = '';
            dom.tableDataContainer.innerHTML = '';
            // Inject the fetched HTML into the main content container.
            container.innerHTML = data;
        },
        // Callback function for handling errors.
        error: (errorCode, errorMessage, container, dom) => {
            console.error(error);
        },
        // A general render function (can be used for static content).
        render: (data, container, dom) => {
            // Not used here as content is fetched via URL.
        }
    };
    graphqlApp.pages['settings-update'] = {
        url: 'settings-update.php',
        title: 'settings', // The translation key for the page title.
        method: 'GET',
        headers: {
            'X-Language-Id': graphqlApp.languageId,
            'Accept-Language': graphqlApp.languageId
        },
        accept: 'text/html',
        // Callback function executed on a successful fetch.
        success: (data, container, dom) => {
            // Hide standard entity view elements.
            dom.filterContainer.style.display = 'none';
            dom.paginationContainer.style.display = 'none';
            dom.filterContainer.innerHTML = '';
            dom.tableDataContainer.innerHTML = '';
            // Inject the fetched HTML into the main content container.
            container.innerHTML = data;
        },
        // Callback function for handling errors.
        error: (errorCode, errorMessage, container, dom) => {
            console.error(error);
        },
        // A general render function (can be used for static content).
        render: (data, container, dom) => {
            // Not used here as content is fetched via URL.
        }
    };
    graphqlApp.pages['message'] = {
        url: 'message.php',
        title: 'message', // The translation key for the page title.
        method: 'GET',
        headers: {
            'X-Language-Id': graphqlApp.languageId,
            'Accept-Language': graphqlApp.languageId
        },
        accept: 'text/html',
        // Callback function executed on a successful fetch.
        success: (data, container, dom) => {
            // Hide standard entity view elements.
            dom.filterContainer.style.display = 'none';
            dom.paginationContainer.style.display = 'none';
            dom.filterContainer.innerHTML = '';
            dom.tableDataContainer.innerHTML = '';
            // Inject the fetched HTML into the main content container.
            container.innerHTML = data;
        },
        // Callback function for handling errors.
        error: (errorCode, errorMessage, container, dom) => {
            console.error(error);
        },
        // A general render function (can be used for static content).
        render: (data, container, dom) => {
            // Not used here as content is fetched via URL.
        }
    };
    graphqlApp.pages['notification'] = {
        url: 'notification.php',
        title: 'notification', // The translation key for the page title.
        method: 'GET',
        headers: {
            'X-Language-Id': graphqlApp.languageId,
            'Accept-Language': graphqlApp.languageId
        },
        accept: 'text/html',
        // Callback function executed on a successful fetch.
        success: (data, container, dom) => {
            // Hide standard entity view elements.
            dom.filterContainer.style.display = 'none';
            dom.paginationContainer.style.display = 'none';
            dom.filterContainer.innerHTML = '';
            dom.tableDataContainer.innerHTML = '';
            // Inject the fetched HTML into the main content container.
            container.innerHTML = data;
        },
        // Callback function for handling errors.
        error: (errorCode, errorMessage, container, dom) => {
            console.error(error);
        },
        // A general render function (can be used for static content).
        render: (data, container, dom) => {
            // Not used here as content is fetched via URL.
        }
    }

    graphqlApp.pages['update-password'] = {
        url: 'update-password.php',
        title: 'update_password', // The translation key for the page title.
        method: 'GET',
        headers: {
            'X-Language-Id': graphqlApp.languageId,
            'Accept-Language': graphqlApp.languageId
        },
        accept: 'text/html',
        // Callback function executed on a successful fetch.
        success: (data, container, dom) => {
            // Hide standard entity view elements.
            dom.filterContainer.style.display = 'none';
            dom.paginationContainer.style.display = 'none';
            dom.filterContainer.innerHTML = '';
            dom.tableDataContainer.innerHTML = '';
            // Inject the fetched HTML into the main content container.
            container.innerHTML = data;
        },
        // Callback function for handling errors.
        error: (errorCode, errorMessage, container, dom) => {
            console.error(errorCode, errorMessage);
        },
        render: (data, container, dom) => {
            // Not used here as content is fetched via URL.
        }
    };

});

window.addEventListener('hashchange', () => {
    const hash = location.hash;

    // Simpan halaman list terakhir untuk #message
    if (hash.startsWith('#message') && !hash.includes('messageId=')) {
        sessionStorage.setItem('lastMessageListUrl', location.href);
    }

    // Simpan halaman list terakhir untuk #notification
    if (hash.startsWith('#notification') && !hash.includes('notificationId=')) {
        sessionStorage.setItem('lastNotificationListUrl', location.href);
    }
});



function backToList(type) {
    const storageKey = type === 'notification'
        ? 'lastNotificationListUrl'
        : 'lastMessageListUrl';

    const lastListUrl = sessionStorage.getItem(storageKey);

    if (lastListUrl && lastListUrl !== location.href) {
        location.href = lastListUrl;
    } else {
        history.pushState(null, '', `#${type}`);
        graphqlApp.handleRouteChange();
    }
}

async function handleProfileUpdate(event) {
    const form = document.getElementById('profile-update-form');
    const formData = new FormData(form);
    try {
        const response = await fetch('user-profile.php', {
            method: 'POST',
            headers:{
                'X-Language-Id': graphqlApp.languageId,
                'Accept-Language': graphqlApp.languageId
            },
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            await graphqlApp.customAlert({ title: graphqlApp.t('success'), message: result.message });
            window.location.hash = '#user-profile';
        } else {
            await graphqlApp.customAlert({ title: graphqlApp.t('error'), message: result.message });
        }
    } catch (error) {
        console.error('Error updating profile:', error);
        await graphqlApp.customAlert({ title: graphqlApp.t('error'), message: 'An unexpected error occurred.' });
    }
}

async function handlePasswordUpdate(event) {
    const form = document.getElementById('password-update-form');
    const formData = new FormData(form);
    try {
        const response = await fetch('update-password.php', {
            method: 'POST',
            headers:{
                'X-Language-Id': graphqlApp.languageId,
                'Accept-Language': graphqlApp.languageId
            },
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            await graphqlApp.customAlert({ title: graphqlApp.t('success'), message: result.message });
            window.location.hash = '#user-profile';
        } else {
            await graphqlApp.customAlert({ title: graphqlApp.t('error'), message: result.message });
        }
    } catch (error) {
        console.error('Error updating password:', error);
        await graphqlApp.customAlert({ title: graphqlApp.t('error'), message: 'An unexpected error occurred.' });
    }
}

async function handleSettingsUpdate(event) {
    const form = document.getElementById('settings-update-form');
    const formData = new FormData(form);
    try {
        const response = await fetch('settings.php', {
            method: 'POST',
            headers:{
                'X-Language-Id': graphqlApp.languageId,
                'Accept-Language': graphqlApp.languageId
            },
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            await graphqlApp.customAlert({ title: graphqlApp.t('success'), message: result.message });
            window.location.hash = '#settings';
        } else {
            await graphqlApp.customAlert({ title: graphqlApp.t('error'), message: result.message });
        }
    } catch (error) {
        console.error('Error updating settings:', error);
        await graphqlApp.customAlert({ title: graphqlApp.t('error'), message: 'An unexpected error occurred.' });
    }
}