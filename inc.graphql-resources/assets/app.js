document.addEventListener('DOMContentLoaded', () => {
    // Create a single instance of the application.
    // The constructor will handle the entire initialization process.    
    new GraphQLClientApp({

        // Backend URLs
        configUrl: 'frontend-config.php',
        apiUrl: 'graphql.php',
        loginUrl: 'login.php',
        logoutUrl: 'logout.php',
        entityLanguageUrl: 'entity-language.php',
        i18nUrl: 'language.php',
        languageConfigUrl: 'available-language.php',

        defaultActiveField: 'active',
        defaultDisplayField: 'name',
        
        languageId: null,
        defaultLanguage: 'en',

        customRenderers: {},
    });
});
