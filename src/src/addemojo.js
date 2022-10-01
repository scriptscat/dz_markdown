// 显示添加表情包按钮

window.addEventListener("load", () => {
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
