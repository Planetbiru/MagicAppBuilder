function doStuff() {
    for (let i = 1; i <= 6; i++) {
        let headers = document.getElementsByTagName('h' + i);
        for (let j = 0; j < headers.length; j++) {
            headers[j].className = 'h';
            headers[j].setAttribute('data-id', i*100+j)
        }
    }
    let headers = document.getElementsByClassName('h');
    let h1 = document.getElementsByTagName('h1')[0];
    h1.parentNode.insertBefore(document.createElement('ul'), h1.nextSibling);
    h1.nextSibling.id = 'nav';
    let ul = document.querySelector('#nav ul');
    for (let header of headers) {
        let id = header.getAttribute('data-id');
        ul.innerHTML += ('<li class="' + header.tagName.toLowerCase() + '"><a href="javascript:goto('+id+')">' + header.innerHTML + '</a></li>');
    }
}

function goto(id)
{
    let obj = document.querySelector('[data-id="'+id+'"]');
    let top = obj.offsetTop;
    window.scrollTo({
        top: top - 10,
        behavior: "smooth",
      });
}