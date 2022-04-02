import '@toast-ui/editor/dist/toastui-editor.css';

import Editor from '@toast-ui/editor';

window.initeditor = function (editor) {
    let mdDiv = document.querySelector('#mk-editor');
    if (!mdDiv) {
        return;
    }
    let dzDiv = document.querySelector('.edt');
    if (editor !== 'md') {
        mdDiv.style.display = 'none';
    } else {
        dzDiv.style.setProperty('display', 'none', 'important');
    }
    let html = mdDiv.innerHTML;

    mdDiv.innerHTML = '';
    const md = new Editor({
        el: mdDiv,
        height: '500px',
        initialEditType: 'markdown',
        previewStyle: 'vertical'
    });
    md.setMarkdown(html);

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
