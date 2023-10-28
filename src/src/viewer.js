import 'github-markdown-css/github-markdown-light.css';
import './viewer.css';
import './addemojo';
import Prism from 'prismjs';

import './prismjs';

Prism.highlightAllUnder(document, true);

window.addEventListener("DOMContentLoaded", () => {
    document.querySelector('.markdown-body').addEventListener('click', (ev) => {
        if (ev.target.tagName === 'IMG' && ev.target.className === 'md-img') {
            zoom(ev.target, ev.target.src, 0, 0, 0)
            return;
        } else if (ev.target.className === 'octicon octicon-link') {
            ev.stopPropagation()
            ev.preventDefault()
            console.log(ev)
            scrollIntoView(ev.target.parentNode.hash.replace('#', ''));
            return false;
        } else if (ev.target.tagName === 'A' && ev.target.getAttribute('href').startsWith('#')) {
            // dz太坑了,有个base标签,导致hash不对
            ev.stopPropagation()
            ev.preventDefault()
            scrollIntoView(ev.target.hash.replace('#', ''));
            return false;
        }
    })
    // 判断链接中是否有锚点
    let hash = location.hash.replace('#user-content-', '')
    let el = document.querySelector('#user-content-' + hash)
    if (el) {
        el.scrollIntoView(true)
    }
})

window.scrollIntoView = function (id) {
    let el = document.getElementById('user-content-' + id)
    if (el) {
        el.scrollIntoView(true)
    }
}
