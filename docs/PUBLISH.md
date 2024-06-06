# 发布到商店

```bash
# 在src的上级目录执行
ln -s src codfrm_markdown
zip -r codfrm_markdown.zip codfrm_markdown -x "codfrm_markdown/node_modules/*"
rm codfrm_markdown
```