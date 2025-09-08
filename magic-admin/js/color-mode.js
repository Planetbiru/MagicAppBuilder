let templateColorMode = window.localStorage.getItem('MagicAdmin.colorMode');
let templateHideSidebar = window.localStorage.getItem('MagicAdmin.sidebarHidden');
let themeDark = '#212529';
let themeLight = '#f8f9fa';
if(templateColorMode == null || templateColorMode == '')
{
    templateColorMode = 'light-mode';
}
document.querySelector('body').classList.add(templateColorMode);
if(templateHideSidebar == 'true')
{
    document.querySelector('body').classList.add('sidebar-hidden');
}
