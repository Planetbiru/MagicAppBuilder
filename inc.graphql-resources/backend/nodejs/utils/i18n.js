const path = require('path');
const fs = require('fs');

// Cache for loaded language files to avoid reading from disk repeatedly
const langCache = {};

// Helper function for sanitizing HTML output
const esc = (str) => {
    if (str === null || typeof str === 'undefined') return '';
    return String(str).replace(/[&<>"']/g, (match) => {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[match];
    });
};

const loadLanguage = (langId) => {
    if (langCache[langId]) {
        return langCache[langId];
    }

    const langFilePath = path.join(__dirname, '../public/langs/i18n', `${langId}.json`);

    try {
        const langData = fs.readFileSync(langFilePath, 'utf8');
        const translations = JSON.parse(langData);
        langCache[langId] = translations;
        return translations;
    } catch (error) {
        console.error(`Failed to load language file for ${langId}:`, error.message);

        // Fallback to 'en' (English) if the requested language file is not found
        if (langId !== 'en') {
            return loadLanguage('en'); // Recursive fallback to English
        }

        return {}; // Return empty object if even the default fails
    }
};

const getTranslator = (req) => {
    const langId = req.headers['x-language-id'] || 'en'; // Default to English

    const translations = loadLanguage(langId);

    return (key, ...args) => {
        let translatedText = translations[key] || key; // Fallback to key if translation missing

        // Replace placeholders {0}, {1}, etc.
        args.forEach((arg, index) => {
            translatedText = translatedText.replace(new RegExp(`\\{${index}\\}`, 'g'), arg);
        });

        return translatedText;
    };
};

module.exports = { getTranslator, esc };
