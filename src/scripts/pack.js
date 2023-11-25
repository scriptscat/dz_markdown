// 打包为gbk版本
const fs = require('fs');
const os = require("os");
const {execSync} = require("child_process");
let iconv = require('iconv-lite');
const path = require("path");

// 先执行npm run build
execSync('npm run build');

// 读取dist目录下的文件,gbk编码,放到dist/gbk目录下
const distPath = "./dist";

// 创建dist/gbk/dist目录
if (!fs.existsSync(path.join(distPath, "gbk"))) {
    fs.mkdirSync(path.join(distPath, "gbk"));
}
if (!fs.existsSync(path.join(distPath, "gbk", "dist"))) {
    fs.mkdirSync(path.join(distPath, "gbk", "dist"));
}

function forEachFile(files, dealFile) {
    files.forEach((file) => {
        if (file.endsWith("/*")) {
            const dir = file.substring(0, file.length - 2);
            fs.readdirSync(dir).forEach((subFile) => {
                const filePath = path.join(dir, subFile);
                // 判断是否是目录
                if (fs.statSync(filePath).isDirectory()) {
                    return;
                }
                dealFile(dir, subFile, fs.readFileSync(filePath));
            });
        } else {
            const dir = path.dirname(file);
            file = path.basename(file);
            dealFile(dir, file, fs.readFileSync(path.join(dir, file)));
        }
    });
}

forEachFile(["./dist/*"], (dir, filename, data) => {
    // {Aacute:(.*?)},替换为{}
    data = data.toString();
    data = data.replaceAll(/={Aacute:(.*?)},/g, "={},");
    const gbkData = iconv.encode(data, "gbk");
    if (filename === "editor.js") {
        // 去除影响gbk编码的内容
    }
    fs.writeFileSync(path.join(dir, "gbk", "dist", filename), gbkData);
});

// 复制php相关文件
const phpFiles = [
    "emoji.inc.php",
    "markdown.class.php",
    "discuz_plugin_codfrm_markdown.xml",
    "discuz_plugin_codfrm_markdown_SC_GBK.xml",
    "discuz_plugin_codfrm_markdown_SC_UTF8.xml",
    "src/ParsedownExt.php",
    "table/*",
    "template/*"
];

forEachFile(phpFiles, (dir, file, data) => {
    const gbkData = iconv.encode(data, "gbk");
    // 创建目录
    if (dir && !fs.existsSync(path.join("dist", "gbk", dir))) {
        fs.mkdirSync(path.join("dist", "gbk", dir));
    }
    fs.writeFileSync(path.join("dist", "gbk", dir, file), gbkData);
});

// 复制vendor
execSync("cp -r vendor dist/gbk/");

// 复制安装/更新文件
execSync("cp -r scripts/gbk/* dist/gbk/");