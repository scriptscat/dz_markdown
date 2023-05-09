import 'github-markdown-css/github-markdown-light.css';
import './viewer.css';
import './addemojo';
import Prism from 'prismjs';

import './prismjs';

Prism.highlightAllUnder(document, true);

window.addEventListener("DOMContentLoaded", () => {
    jQuery(document).click('.markdown-body img', function (ev) {
        if (ev.target.tagName !== 'IMG' || ev.target.className !== 'md-img') {
            return;
        }
        zoom(ev.target, ev.target.src, 0, 0, 0)
    });
})
