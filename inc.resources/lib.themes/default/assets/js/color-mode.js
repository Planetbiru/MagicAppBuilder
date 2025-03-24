let templateColorMode = window.localStorage.getItem('MagicAppBuilder.colorMode');
let templateHideSidebar = window.localStorage.getItem('MagicAppBuilder.sidebarHidden');
if(templateColorMode == null || templateColorMode == '')
{
    templateColorMode = 'light-mode';
}
document.querySelector('body').classList.add(templateColorMode);
if(templateHideSidebar == 'true')
{
    document.querySelector('body').classList.add('sidebar-hidden');
}
