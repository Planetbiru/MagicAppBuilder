let templateColorMode = window.localStorage.getItem('MagicAppBuilder.colorMode');
let templateHideSidebar = window.localStorage.getItem('MagicAppBuilder.sidebarHidden');
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
