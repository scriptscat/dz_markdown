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
            const hash = ev.target.hash.replace('#', '');
            scrollIntoView(hash);
            // url改变
            return false;
        }
    })
    // 判断链接中是否有锚点
    let hash = location.hash;
    let el = document.querySelector('#user-content-' + hash.substring(1))
    if (el) {
        const yOffset = -50;
        const y = el.getBoundingClientRect().top + window.scrollY + yOffset;
        window.scrollTo({top: y, behavior: 'smooth'});
    }
})

window.scrollIntoView = function (id) {
    let el = document.getElementById('user-content-' + id)
    if (el) {
        location.hash = id;
        // 处理偏移
        const yOffset = -50;
        const y = el.getBoundingClientRect().top + window.scrollY + yOffset;
        window.scrollTo({top: y, behavior: 'smooth'});
    }
}
