let templateColorMode = window.localStorage.getItem('MagicAppBuilder.colorMode');
let templateHideSidebar = window.localStorage.getItem('MagicAppBuilder.sidebarHidden');
let themeDark = '#212529';
let themeLight = '#f8fbff';
if(templateColorMode == null || templateColorMode == '')
{
    templateColorMode = 'light-mode';
}

if(templateColorMode == 'dark-mode')
{
    document.querySelector('meta[name="theme-color"]').setAttribute('content', themeDark);
}
else
{
    document.querySelector('meta[name="theme-color"]').setAttribute('content', themeLight);
}

document.querySelector('body').classList.add(templateColorMode);
if(templateHideSidebar == 'true')
{
    document.querySelector('body').classList.add('sidebar-hidden');
}
