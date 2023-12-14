import 'github-markdown-css/github-markdown-light.css';
import './viewer.css';

function enableEmoji() {
    window.addEventListener("DOMContentLoaded", () => {
        let mk_bodys = document.querySelectorAll('.markdown-body')
        mk_bodys.forEach(mk_body => {
            let div_wrap = undefined
            mk_body.addEventListener('mouseover', (evt) => {
                if (evt.target.className.indexOf('add-emoji') === -1) {
                    if (div_wrap) {
                        div_wrap.remove()
                        div_wrap = undefined;
                    }
                }
                if (evt.target.tagName === "IMG") {
                    div_wrap = document.createElement("div");
                    div_wrap.className = 'add-emoji-back'
                    div_wrap.addEventListener('mouseleave', (evt) => {
                        if (div_wrap) {
                            div_wrap.remove()
                            div_wrap = undefined;
                        }
                    })
                    let button = document.createElement("div")
                    button.className = 'add-emoji'
                    button.innerText = '保存为表情包'
                    div_wrap.appendChild(button)
                    let top = evt.target.getBoundingClientRect().top + document.documentElement.scrollTop;
                    top = top - 28
                    let left = evt.target.getBoundingClientRect().left + document.documentElement.scrollLeft;
                    div_wrap.style = "position:absolute;top:" + top + "px;left:" + left + "px;height:28px;"
                    let current_img = evt.target
                    div_wrap.onclick = function (event) {
                        fetch('/plugin.php?id=codfrm_markdown:emoji&op=add', {
                            method: 'POST',
                            body: 'url=' + encodeURIComponent(current_img.src),
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            }
                        }).then((response) => {
                            response.json().then((json) => {
                                if (json.data === 'ok') {
                                    alert('保存成功')
                                } else {
                                    alert('保存失败')
                                }
                            }).catch(err => {
                                alert("保存失败:" + err)
                            });
                        })

                    };
                    mk_body.append(div_wrap);
                }
            })
            mk_body.addEventListener('mouseleave', (evt) => {
                if (div_wrap) {
                    div_wrap.remove()
                    div_wrap = undefined;
                }
            })
        })
    })
}

window.markdownView = function (opts) {
    if (opts.enableEmoji) {
        enableEmoji();
    }
}

window.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('.markdown-body').forEach(ev => {
        ev.addEventListener('click', (ev) => {
            if (ev.target.tagName === 'IMG' && ev.target.className === 'md-img') {
                zoom(ev.target, ev.target.src, 0, 0, 0)
                return;
            } else if (ev.target.className === 'octicon octicon-link') {
                ev.stopPropagation()
                ev.preventDefault()
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
        let el = document.querySelector('#user-content-' +
            hash.substring(1).replaceAll('%', '\\%'));
        if (el) {
            const yOffset = -50;
            const y = el.getBoundingClientRect().top + window.scrollY + yOffset;
            window.scrollTo({top: y, behavior: 'smooth'});
        }
    })
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
