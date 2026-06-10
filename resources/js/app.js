import {translations} from './i18n';
import {initUploadForm} from './upload';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

let currentLanguage = localStorage.getItem('private-transfer-language') || 'de';
let currentTheme = localStorage.getItem('private-transfer-theme') || 'light';

const t = (key) => translations[currentLanguage]?.[key] || translations.de[key] || key;

const revealText = (element, value) => {
    if (!element || element.textContent === value) {
        return;
    }

    element.textContent = value;
    element.classList.remove('reveal');
    void element.offsetWidth;
    element.classList.add('reveal');
};

const applyLanguage = (language) => {
    currentLanguage = translations[language] ? language : 'de';
    localStorage.setItem('private-transfer-language', currentLanguage);
    document.documentElement.lang = currentLanguage;

    document.querySelectorAll('[data-i18n]').forEach((element) => {
        element.textContent = t(element.dataset.i18n);
    });

    document.querySelectorAll('[data-i18n-placeholder]').forEach((element) => {
        element.setAttribute('placeholder', t(element.dataset.i18nPlaceholder));
    });

    document.querySelectorAll('[data-language-option]').forEach((button) => {
        button.classList.toggle('is-active', button.dataset.languageOption === currentLanguage);
    });

    document.querySelector('[data-theme-label]') && (document.querySelector('[data-theme-label]').textContent = document.documentElement.classList.contains('dark') ? t('light') : t('dark'));
};

const applyTheme = (theme) => {
    currentTheme = theme === 'dark' ? 'dark' : 'light';
    localStorage.setItem('private-transfer-theme', currentTheme);
    document.documentElement.classList.toggle('dark', currentTheme === 'dark');
    document.querySelector('[data-theme-label]') && (document.querySelector('[data-theme-label]').textContent = currentTheme === 'dark' ? t('light') : t('dark'));
};

document.querySelectorAll('[data-language-option]').forEach((button) => {
    button.addEventListener('click', () => applyLanguage(button.dataset.languageOption));
});

document.querySelector('[data-theme-toggle]')?.addEventListener('click', () => {
    applyTheme(currentTheme === 'dark' ? 'light' : 'dark');
});

document.querySelectorAll('[data-copy-link]').forEach((button) => {
    button.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(button.dataset.copyLink);
        } catch {
            document.querySelector('#download_link')?.select();
            document.execCommand('copy');
        }
        button.textContent = t('copied');
        setTimeout(() => {
            button.textContent = t('copyLink');
        }, 1600);
    });
});

applyTheme(currentTheme);
applyLanguage(currentLanguage);

initUploadForm({csrfToken, revealText, t});
