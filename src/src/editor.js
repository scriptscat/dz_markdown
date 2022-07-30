import '@toast-ui/editor/dist/toastui-editor.css';
import 'prismjs/themes/prism.css';
import '@toast-ui/editor-plugin-code-syntax-highlight/dist/toastui-editor-plugin-code-syntax-highlight.css';
import Editor from '@toast-ui/editor';
import '@toast-ui/editor/dist/i18n/zh-cn';
import codeSyntaxHighlight from '@toast-ui/editor-plugin-code-syntax-highlight';
import Prism from 'prismjs';
import "./prismjs";
import "./viewer.css";


window.initeditor = function (postid, editor) {
    let mdDiv = document.querySelector('#md');
    if (!mdDiv) {
        return;
    }
    let mdEditor = document.querySelector('#md-editor');
    let saveEl = document.querySelector('#md-autosave');
    let dzDiv = document.querySelector('.edt');
    if (editor !== 'md') {
        mdDiv.style.display = 'none';
    } else {
        dzDiv.style.setProperty('display', 'none', 'important');
    }
    let html = mdEditor.innerText;
    mdEditor.innerText = '';
    if (localStorage['md-autosave-' + postid]) {
        html = localStorage['md-autosave-' + postid];
    }

    function emojiBtn() {
        const button = document.createElement('button');
        button.className = 'toastui-editor-toolbar-icons last';
        button.style.backgroundImage = 'none';
        button.style.margin = '0';
        button.innerHTML = `<i>😊</i>`;
        button.type = "button";
        return button;
    }

    const emoji = showEmoji();

    const md = new Editor({
        el: mdEditor,
        initialValue: html,
        height: '500px',
        initialEditType: 'markdown',
        previewStyle: 'vertical',
        toolbarItems: [['heading', 'bold', 'italic', 'strike', {
            el: emojiBtn(),
            command: 'emoji',
            tooltip: '表情包',
            popup: {
                body: emoji.body(),
                className: 'toastui-editor-popup-add-image',
            },

        }],
            ['hr', 'quote'],
            ['ul', 'ol', 'task', 'indent', 'outdent'],
            ['table', 'image', 'link'],
            ['code', 'codeblock']],
        hooks: {
            addImageBlobHook: async (blob, callback) => {
                const uploadedImageURL = await uploadImage(blob);
                callback(uploadedImageURL, blob.name);
                return false;
            },
        },
        plugins: [
            [codeSyntaxHighlight, {highlighter: Prism}],
        ],
        autofocus: false,
        language: 'zh-CN',
    });
    emoji.setMd(md);
    setInterval(() => {
        localStorage['md-autosave-' + postid] = md.getMarkdown();
        saveEl.innerHTML = "已自动保存 " + new Date().toLocaleTimeString();
    }, 10000);
    document.querySelector('#switch-editor').onclick = function () {
        if (editor === 'md') {
            this.innerHTML = '使用狂炫酷爆吊炸天的markdown编辑器';
            editor = 'dz';
            mdDiv.style.display = 'none';
            dzDiv.style.display = 'block';
        } else {
            this.innerHTML = '使用默认编辑器';
            editor = 'md';
            dzDiv.style.setProperty('display', 'none', 'important');
            mdDiv.style.display = 'block';
        }
        return false;
    }
    document.querySelector('#postform').addEventListener("submit", function () {
        localStorage.removeItem('md-autosave-' + postid);
        return true;
    });
    // hook getEditorContents 方法
    let getEditorContents = window.getEditorContents;
    window.getEditorContents = function () {
        if (editor === 'md') {
            return "[md]" + md.getMarkdown() + "[/md]";
        }
        return getEditorContents();
    }

    let html2bbcode = window.html2bbcode;
    window.html2bbcode = function (str) {
        if (editor === 'md') {
            return str;
        }
        return html2bbcode(str);
    }
}

function emojiList() {
    return {
        "人物": [
            "😀", "😃", "😄", "😁", "😆", "😅", "😂", "🤣", "😇", "😉", "😊", "🙂", "🙃", "😋", "😌", "😍", "🥰", "😘", "😗", "😙", "😚", "🥲", "🤪", "😜", "😝", "😛", "🤑", "😎", "🤓", "🥸", "🧐", "🤠", "🥳", "🤡", "😏", "😶", "😐", "🫤", "😑", "😒", "🙄", "🤨", "🤔", "🤫", "🤭", "🫢", "🫡", "🤗", "🫣", "🤥", "😳", "😞", "😟", "😤", "😠", "😡", "🤬", "😔", "😕", "🙁", "😬", "🥺", "😣", "😖", "😫", "😩", "🥱", "😪", "😮‍💨", "😮", "😱", "😨", "😰", "😥", "😓", "😯", "😦", "😧", "🥹", "😢", "😭", "🤤", "🤩", "😵", "😵‍💫", "🥴", "😲", "🤯", "🫠", "🤐", "😷", "🤕", "🤒", "🤮", "🤢", "🤧", "🥵", "🥶",
        ],
        "动物": [
            "🐶", "🐱", "🐭", "🐹", "🐰", "🐻", "🧸", "🐼", "🐻‍❄️", "🐨", "🐯", "🦁", "🐮", "🐷", "🐽", "🐸", "🐵", "🙈", "🙉", "🙊", "🐒", "🦍", "🦧", "🐔", "🐧", "🐦", "🐤", "🐣", "🐥", "🐺", "🦊", "🦝", "🐗", "🐴", "🦓", "🦒", "🦌", "🦘", "🦥", "🦦", "🦫", "🦄", "🐝", "🐛", "🦋", "🐌", "🪲", "🐞", "🐜", "🦗", "🪳", "🕷", "🕸", "🦂", "🦟", "🪰", "🪱", "🦠", "🐢", "🐍", "🦎", "🐙", "🦑", "🦞", "🦀", "🦐", "🦪", "🐠", "🐟", "🐡", "🐬", "🦈", "🦭", "🐳", "🐋", "🐊", "🐆", "🐅", "🐃", "🐂", "🐄", "🦬", "🐪", "🐫", "🦙", "🐘", "🦏", "🦛", "🦣", "🐐", "🐏", "🐑", "🐎", "🐖", "🦇", "🐓", "🦃", "🕊", "🦅", "🦆", "🦢", "🦉", "🦩", "🦚", "🦜", "🦤", "🪶", "🐕", "🦮", "🐕‍🦺", "🐩", "🐈", "🐈‍⬛", "🐇", "🐀", "🐁", "🐿", "🦨", "🦡", "🦔", "🐾", "🐉", "🐲", "🦕", "🦖", "🌵", "🎄", "🌲", "🌳", "🌴", "🪴", "🌱", "🌿", "☘", "🍀", "🎍", "🎋", "🍃", "🍂", "🍁", "🌾", "🪺", "🪹", "🌺", "🌻", "🌹", "🥀", "🌷", "🌼", "🌸", "🪷", "💐", "🍄", "🐚", "🪸", "🌎", "🌍", "🌏", "🌕", "🌖", "🌗", "🌘", "🌑", "🌒", "🌓", "🌔", "🌙", "🌚", "🌝", "🌛", "🌜", "⭐", "🌟", "💫", "✨", "☄", "🪐", "🌞", "☀", "🌤", "⛅", "🌥", "🌦", "☁", "🌧", "⛈", "🌩", "⚡", "🔥", "💥", "❄", "🌨", "☃", "⛄", "🌬", "💨", "🌪", "🌫", "🌈", "☔", "💧", "💦", "🌊"
        ],
        "食物": [
            "🍏", "🍎", "🍐", "🍊", "🍋", "🍌", "🍉", "🍇", "🍓", "🍈", "🍒", "🫐", "🍑", "🥭", "🍍", "🥥", "🥝", "🍅", "🥑", "🫒", "🍆", "🌶", "🫑", "🥒", "🥬", "🥦", "🧄", "🧅", "🌽", "🥕", "🥗", "🥔", "🍠", "🌰", "🥜", "🫘", "🍯", "🍞", "🥐", "🥖", "🫓", "🥨", "🥯", "🥞", "🧇", "🧀", "🍗", "🍖", "🥩", "🍤", "🥚", "🍳", "🥓", "🍔", "🍟", "🌭", "🍕", "🍝", "🥪", "🌮", "🌯", "🫔", "🥙", "🧆", "🍜", "🥘", "🍲", "🫕", "🥫", "🫙", "🧂", "🧈", "🍥", "🍣", "🍱", "🍛", "🍙", "🍚", "🍘", "🥟", "🍢", "🍡", "🍧", "🍨", "🍦", "🍰", "🎂", "🧁", "🥧", "🍮", "🍭", "🍬", "🍫", "🍿", "🍩", "🍪", "🥠", "🥮", "☕", "🍵", "🫖", "🥣", "🍼", "🥤", "🧋", "🧃", "🧉", "🥛", "🫗", "🍺", "🍻", "🍷", "🥂", "🥃", "🍸", "🍹", "🍾", "🍶", "🧊", "🥄", "🍴", "🍽", "🥢", "🥡",
        ]
    };
}

// 表情包框架
function showEmoji() {
    const ret = {
        md: null,
        setMd: (md) => {
            ret.md = md;
        }
    };
    const div = document.createElement('div');
    const tabs = document.createElement('div');
    tabs.className = 'toastui-editor-tabs';
    const tab1 = document.createElement('div');
    const tab2 = document.createElement('div');
    const tab3 = document.createElement('div');
    tab1.className = 'tab-item active';
    tab1.innerHTML = 'emoji';

    tab2.className = 'tab-item'
    tab2.innerHTML = '自定义';

    tab3.className = 'tab-item';
    tab3.innerHTML = '最近';
    tabs.append(tab1, tab2, tab3);

    const tabDiv1 = document.createElement('div');
    const tabDiv2 = document.createElement('div');
    const tabDiv3 = document.createElement('div');
    tab1.onclick = () => {
        tab1.className = 'tab-item active';
        tab2.className = 'tab-item';
        tab3.className = 'tab-item';
        tabDiv1.style.display = 'block';
        tabDiv2.style.display = 'none';
        tabDiv3.style.display = 'none';
    }
    tab2.onclick = () => {
        tab1.className = 'tab-item';
        tab2.className = 'tab-item active';
        tab3.className = 'tab-item';
        tabDiv1.style.display = 'none';
        tabDiv2.style.display = 'block';
        tabDiv3.style.display = 'none';
    }
    tab3.onclick = () => {
        tab1.className = 'tab-item';
        tab2.className = 'tab-item';
        tab3.className = 'tab-item active';
        tabDiv1.style.display = 'none';
        tabDiv2.style.display = 'none';
        tabDiv3.style.display = 'block';
    }
    const list = emojiList()
    for (let key in list) {
        list[key].forEach(emoji => {
            tabDiv1.append(createEmoji(emoji));
        })
    }
    loadCustom(tabDiv2);
    loadRecent(tabDiv3);
    div.append(tabs, tabDiv1, tabDiv2, tabDiv3);

    function loadCustom(div) {
        const del = function (e) {
            const ok = confirm('确定删除该表情吗？');
            if (!ok) {
                return;
            }
            const el = e.target.parentElement.firstChild;
            fetch('/plugin.php?id=codfrm_markdown:emoji&op=del', {
                method: "POST",
                body: 'url=' + el.getAttribute('src'),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
            e.target.parentElement.remove();
        }
        fetch('/plugin.php?id=codfrm_markdown:emoji&op=custom').then(resp => resp.json()).then(data => {
            data.data.forEach(src => {
                div.append(createEmoji(src.url, true, del));
            })
            const add = document.createElement('div');
            add.className = 'emoji-img';
            add.style.color = '#9c9c9c';
            add.style.textAlign = 'center';
            add.style.padding = '4px';
            add.innerHTML = '<span style="font-size: 48px;">+</span>';
            add.onclick = () => {
                if (div.childElementCount > 50) {
                    alert('最多只能添加50个表情');
                    return;
                }
                const img = prompt('请输入图片地址');
                if (img) {
                    fetch('/plugin.php?id=codfrm_markdown:emoji&op=add', {
                        method: 'POST',
                        body: 'url=' + img,
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        }
                    })
                    div.firstChild.before(createEmoji(img, true, del));
                }
            }
            div.append(add);
        });
    }

    function loadRecent(div) {
        fetch('/plugin.php?id=codfrm_markdown:emoji&op=recent').then(resp => resp.json()).then(data => {
            data.data.forEach(src => {
                div.append(createEmoji('/data/attachment/forum/' + src.url, true));
            })
        });
    }

    function createEmoji(emoji, img, del) {
        const div = document.createElement('div');
        if (img) {
            div.className = 'emoji-img';
            div.innerHTML = '<img src="' + emoji + '"/>';
            if (del) {
                let btn = document.createElement('button');
                btn.className = 'emoji-del';
                btn.innerHTML = 'X';
                btn.onclick = del;
                btn.type = 'button';
                div.append(btn);
            } else {
                div.className += ' un-del';
            }
            div.onclick = function (e) {
                const img = e.currentTarget.firstChild;
                ret.md.insertText("![](" + img.src + ")");
            }
        } else {
            div.className = 'emoji';
            div.innerHTML = emoji;
            div.onclick = function (e) {
                ret.md.insertText(emoji);
            }
        }
        return div;
    }

    ret.body = () => div;
    return ret;
}

function uploadImage(blob) {
    return new Promise((resolve) => {
        let xhr = new XMLHttpRequest();
        xhr.open('POST', "/misc.php?mod=swfupload&action=swfupload&operation=upload&fid=2&simple=2")
        let form = new FormData();
        form.append("uid", discuz_uid);
        form.append("hash", document.querySelector("[name='hash']").value);
        form.append("type", "image")
        form.append("filetype", blob.type);
        form.append("size", blob.size);
        form.append("Filedata", blob);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status !== 200) {
                    return alert("图片上传错误");
                }
                let resps = xhr.responseText.split("|");
                let atta = document.createElement('input');
                atta.name = 'attachnew[' + resps[3] + ']';
                atta.style.display = 'none';
                document.querySelector('#postbox').append(atta);
                resolve(
                    "data/attachment/forum/" + resps[5]
                );
            }
        }
        xhr.send(form)
    });
}