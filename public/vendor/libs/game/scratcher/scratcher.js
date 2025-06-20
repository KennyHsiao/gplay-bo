;
(function ($, window) {
    'use strict';

    $.fn.scratcher = function (options) {
        // 基本配置
        var me = this,
            isSupportCanvas = !!document.createElement('canvas').getContext,
            $canvas = $('<canvas class="scratcher"></canvas>'),
            canvas,// 有滑动事件的canvas
            context,
            CWIDTH = me.width(),
            CHEIGHT = me.height(),
            EventArr = ('ontouchstart' in window) ? ['touchstart', 'touchmove', 'touchend'] : ['mousedown', 'mousemove', 'mouseup'],
            DPR = window.devicePixelRatio || 1,

            settings = $.extend({
                threshold: 40, // 刮掉60的时候显示底图
                lineWidth: 45, // 线宽
            }, options);

        function initCanvas () {
            me.css({
                position: 'relative'
            })
            if (isSupportCanvas) {
                me.append($canvas);
                canvas = $canvas[0];

                context = canvas.getContext("2d");
                // 解决模糊问题
                $canvas.css({
                    width: CWIDTH,
                    height: CHEIGHT,
                    position: 'absolute'
                })
                canvas.width = CWIDTH * DPR;
                canvas.height = CHEIGHT * DPR;
            }
        }

        function initImage ($this) {
            if (settings.backImage) {
               $this.css({
                    'background-image': 'url("' + settings.backImage + '")',
                    'background-size': '100% 100%'
                })
            }
            var image = new Image();
            image.src = settings.maskImage;
            // image.crossOrigin = "Anonymous";
            image.onload = function () {
                context.globalCompositeOperation = 'source-over';
                context.drawImage(image, 0, 0, canvas.width, canvas.height);
                context.globalCompositeOperation = 'destination-out';
            }
        }

        function initPen () {
            // 设置scracherLine
            context.lineWidth = settings.lineWidth;
            context.lineCap = context.lineJoin = 'round';
            context.strokeStyle = 'rgba(0, 0, 0, 1)';
        }

        function getCoord(e) {
            var touch = (EventArr[0].indexOf('touch') > -1) ? e.touches[0] : e,
                offset = e.currentTarget.getBoundingClientRect(),
                x,
                y;
            // 调整位置
            x =  (touch.pageX - offset.left) * DPR;
            y =  (touch.pageY - offset.top) * DPR;
            return {
                x: x,
                y: y
            }
        }

        function startScratch (e) {
            var startXY = getCoord(e);

            context.moveTo(startXY.x, startXY.y);
            // console.log(x * DPR, y * DPR);
            $canvas.on(EventArr[1], moveScratch);
        }

        function moveScratch (e) {
            var startXY = getCoord(e);

            context.lineTo(startXY.x, startXY.y);
            context.stroke();
            // 检测滑动区域 clearRect
            if (scratcherProgress() < settings.threshold) {
                context.clearRect(0, 0, canvas.width, canvas.height);
                context.canvas.onmousemove = null;
                return;
            }
        }

        function endScratch (e) {
            $canvas.off(EventArr[1]);
        }

        /**
         * 检测刮去的比例
         * @return {比例}
         */
        function scratcherProgress () {
            var pixels = context.getImageData(0, 0, canvas.width, canvas.height);
            var pdata = pixels.data;
            var stride = Math.pow(DPR, 2) * 32;
            var l = pdata.length;
            var total = (l / stride);
            var i, count;

            for (i = count = 0; i < l; i += stride) {
                if (pdata[i] != 0) {
                    count++;
                }
            }
            return count*100 / total;
        }

        function bindEvent () {
            // 初始化画笔
            initPen();
            $canvas.on(EventArr[0], startScratch);
            $canvas.on(EventArr[2], endScratch);
        }

        function init ($this) {
            initCanvas();
            // 绘制图片 如果有底图设置底图，没有底图 绘制maskImage 没有 maskImage 绘制 灰色蒙层
            initImage($this);
            bindEvent();
        }

        init(this);

    };
})(Zepto, window);
