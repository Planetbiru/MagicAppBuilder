var cm = window.localStorage.getItem('colorMode');
var hd = window.localStorage.getItem('sidebarHidden');
if(cm == null || cm == '')
{
    cm = 'light-mode';
}
document.querySelector('body').classList.add(cm);
if(hd == 'true')
{
    document.querySelector('body').classList.add('sidebar-hidden');
}
