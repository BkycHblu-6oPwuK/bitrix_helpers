export const body = document.querySelector('body');
export const headerModals = document.querySelectorAll('.header-modal');
export const jsData = JSON.parse(document.getElementById('js-data').textContent);
export const bxSessid = jsData.sessid;
export const mobileMenu = document.querySelector('.m-header__menu');
export const phoneMask = '+ 7 ### ### ## ##';
export const catalogPageUrl = jsData.pages.catalogPageUrl;
export const actionLoadItems = jsData.actionLoadItems;