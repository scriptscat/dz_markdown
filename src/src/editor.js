import '@toast-ui/editor/dist/toastui-editor.css';
import 'prismjs/themes/prism.css';
import '@toast-ui/editor-plugin-code-syntax-highlight/dist/toastui-editor-plugin-code-syntax-highlight.css';

import Editor from '@toast-ui/editor';
import '@toast-ui/editor/dist/i18n/zh-cn';
import codeSyntaxHighlight from '@toast-ui/editor-plugin-code-syntax-highlight';
import Prism from 'prismjs';
import "./prismjs";

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
    const md = new Editor({
        el: mdEditor,
        initialValue: html,
        height: '500px',
        initialEditType: 'markdown',
        previewStyle: 'vertical',
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