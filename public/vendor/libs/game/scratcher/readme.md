## jquery\zepto插件


name: 刮刮乐插件
version: v0.0.1

## 使用方法

这里假设你的页面已经有`jquery或者zepto`，引入`scratcher.css`、`scratcher.js`

html:
```html
<div class="scratcherContainer">
    <!-- 结果 可动态写入 -->
    <!-- <img src="img/background.png" alt=""> -->
</div>
```
脚本:
```js
$('.scratcherContainer').scratcher({
    maskImage: 'img/foreground.png',// 刮层 必填
    threshold: 40, // 刮掉60的时候显示底图
    lineWidth: 45, // 线宽
    backImage: 'img/background.png'// 底图 可以写入HTML标签，也可以配置
});
```
## 效果演示

可以直接git clone下来，双击查看效果，支持桌面环境和移动端，无需配置。

## 注意

因为用到`getImageData`,所以最好放到后端环境中查看，这里可以放到`https://github.com/freeyiyi1993/mobile-test`的public里查看

## todo

使用把canvas画如canvas的方法再实现一遍

## 以上。

