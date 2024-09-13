function doStuff() {
    for (i = 1; i <= 6; i++) {
        var headers = document.getElementsByTagName('h' + i);
        for (j = 0; j < headers.length; j++) {
            headers[j].className = 'h';
            headers[j].setAttribute('data-id', i*100+j)
        }
    }
    var headers = document.getElementsByClassName('h');
    var h1 = document.getElementsByTagName('h1')[0];
    h1.parentNode.insertBefore(document.createElement('ul'), h1.nextSibling);
    h1.nextSibling.id = 'nav';
    let ul = document.querySelector('#nav ul');
    for (i = 0; i < headers.length; i++) {
        let id = headers[i].getAttribute('data-id');
        ul.innerHTML += ('<li class="' + headers[i].tagName.toLowerCase() + '"><a href="javascript:goto('+id+')">' + headers[i].innerHTML + '</a></li>');
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