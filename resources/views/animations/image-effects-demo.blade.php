<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تأثيرات الصور المتحركة - Image Effects Demo</title>
    <link rel="stylesheet" href="{{ asset('css/animations/image-effects.css') }}">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 40px;
            font-size: 2.5em;
        }
        
        .section {
            margin-bottom: 60px;
        }
        
        .section h2 {
            color: #667eea;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .image-container {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            height: 250px;
            background: #f8f9fa;
        }
        
        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.3s ease;
        }
        
        .effect-label {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
            padding: 20px 15px 15px;
            font-weight: bold;
            text-align: center;
        }
        
        .demo-button {
            background: #667eea;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px;
            transition: all 0.3s ease;
        }
        
        .demo-button:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }
        
        .controls {
            text-align: center;
            margin: 40px 0;
        }
        
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 10px 10px 0;
        }
        
        .code-example {
            background: #2d3748;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
        }
        
        .highlight {
            background: #667eea;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎨 تأثيرات الصور المتحركة - Image Effects Demo</h1>
        
        <div class="info-box">
            <h3>📖 كيفية الاستخدام</h3>
            <p>هذا العرض التوضيحي يوضح جميع تأثيرات الصور المتاحة في مكتبة GSAP. يمكنك استخدام هذه التأثيرات في مشروعك بإضافة الكلاسات المناسبة للصور.</p>
        </div>
        
        <!-- تأثيرات التكبير -->
        <div class="section">
            <h2>🔍 تأثيرات التكبير - Zoom Effects</h2>
            <div class="grid">
                <div class="image-container zoom-on-scroll">
                    <img src="https://picsum.photos/400/250?random=1" alt="Zoom on Scroll">
                    <div class="effect-label">تكبير عند التمرير - Zoom on Scroll</div>
                </div>
                <div class="image-container zoom-on-hover">
                    <img src="https://picsum.photos/400/250?random=2" alt="Zoom on Hover">
                    <div class="effect-label">تكبير عند التمرير فوق - Zoom on Hover</div>
                </div>
                <div class="image-container zoom-progressive">
                    <img src="https://picsum.photos/400/250?random=3" alt="Zoom Progressive">
                    <div class="effect-label">تكبير تدريجي - Zoom Progressive</div>
                </div>
            </div>
        </div>
        
        <!-- تأثيرات التحريك -->
        <div class="section">
            <h2>↔️ تأثيرات التحريك - Pan Effects</h2>
            <div class="grid">
                <div class="image-container pan-horizontal">
                    <img src="https://picsum.photos/400/250?random=4" alt="Pan Horizontal">
                    <div class="effect-label">تحريك أفقي - Pan Horizontal</div>
                </div>
                <div class="image-container pan-vertical">
                    <img src="https://picsum.photos/400/250?random=5" alt="Pan Vertical">
                    <div class="effect-label">تحريك عمودي - Pan Vertical</div>
                </div>
                <div class="image-container pan-diagonal">
                    <img src="https://picsum.photos/400/250?random=6" alt="Pan Diagonal">
                    <div class="effect-label">تحريك قطري - Pan Diagonal</div>
                </div>
            </div>
        </div>
        
        <!-- تأثيرات Parallax -->
        <div class="section">
            <h2>🌊 تأثيرات Parallax</h2>
            <div class="grid">
                <div class="image-container parallax-simple">
                    <img src="https://picsum.photos/400/250?random=7" alt="Parallax Simple">
                    <div class="effect-label">Parallax بسيط - Simple Parallax</div>
                </div>
                <div class="image-container parallax-medium">
                    <img src="https://picsum.photos/400/250?random=8" alt="Parallax Medium">
                    <div class="effect-label">Parallax معتدل - Medium Parallax</div>
                </div>
                <div class="image-container parallax-strong">
                    <img src="https://picsum.photos/400/250?random=9" alt="Parallax Strong">
                    <div class="effect-label">Parallax قوي - Strong Parallax</div>
                </div>
            </div>
        </div>
        
        <!-- تأثيرات التلاشي -->
        <div class="section">
            <h2>✨ تأثيرات التلاشي - Fade Effects</h2>
            <div class="grid">
                <div class="image-container fade-simple">
                    <img src="https://picsum.photos/400/250?random=10" alt="Fade Simple">
                    <div class="effect-label">تلاشي بسيط - Simple Fade</div>
                </div>
                <div class="image-container fade-from-top">
                    <img src="https://picsum.photos/400/250?random=11" alt="Fade from Top">
                    <div class="effect-label">تلاشي من الأعلى - Fade from Top</div>
                </div>
                <div class="image-container fade-from-bottom">
                    <img src="https://picsum.photos/400/250?random=12" alt="Fade from Bottom">
                    <div class="effect-label">تلاشي من الأسفل - Fade from Bottom</div>
                </div>
            </div>
        </div>
        
        <!-- تأثيرات الدوران -->
        <div class="section">
            <h2>🔄 تأثيرات الدوران - Rotation Effects</h2>
            <div class="grid">
                <div class="image-container rotate-simple">
                    <img src="https://picsum.photos/400/250?random=13" alt="Rotate Simple">
                    <div class="effect-label">دوران بسيط - Simple Rotation</div>
                </div>
                <div class="image-container rotate-continuous">
                    <img src="https://picsum.photos/400/250?random=14" alt="Rotate Continuous">
                    <div class="effect-label">دوران مستمر - Continuous Rotation</div>
                </div>
                <div class="image-container rotate-3d">
                    <img src="https://picsum.photos/400/250?random=15" alt="Rotate 3D">
                    <div class="effect-label">دوران ثلاثي الأبعاد - 3D Rotation</div>
                </div>
            </div>
        </div>
        
        <!-- تأثيرات 2.5D -->
        <div class="section">
            <h2>🎭 تأثيرات 2.5D</h2>
            <div class="grid">
                <div class="image-container effect-2-5d-simple">
                    <img src="https://picsum.photos/400/250?random=16" alt="2.5D Simple">
                    <div class="effect-label">2.5D بسيط - Simple 2.5D</div>
                </div>
                <div class="image-container effect-2-5d-move">
                    <img src="https://picsum.photos/400/250?random=17" alt="2.5D Move">
                    <div class="effect-label">2.5D مع تحريك - 2.5D Move</div>
                </div>
                <div class="image-container effect-2-5d-scale">
                    <img src="https://picsum.photos/400/250?random=18" alt="2.5D Scale">
                    <div class="effect-label">2.5D مع تكبير - 2.5D Scale</div>
                </div>
            </div>
        </div>
        
        <!-- تأثيرات التمرير -->
        <div class="section">
            <h2>🖱️ تأثيرات التمرير - Hover Effects</h2>
            <div class="grid">
                <div class="image-container hover-simple">
                    <img src="https://picsum.photos/400/250?random=19" alt="Hover Simple">
                    <div class="effect-label">تمرير بسيط - Simple Hover</div>
                </div>
                <div class="image-container hover-rotate">
                    <img src="https://picsum.photos/400/250?random=20" alt="Hover Rotate">
                    <div class="effect-label">تمرير مع دوران - Hover Rotate</div>
                </div>
                <div class="image-container hover-fade">
                    <img src="https://picsum.photos/400/250?random=21" alt="Hover Fade">
                    <div class="effect-label">تمرير مع تلاشي - Hover Fade</div>
                </div>
            </div>
        </div>
        
        <!-- تأثيرات النقر -->
        <div class="section">
            <h2>👆 تأثيرات النقر - Click Effects</h2>
            <div class="grid">
                <div class="image-container click-simple">
                    <img src="https://picsum.photos/400/250?random=22" alt="Click Simple">
                    <div class="effect-label">نقر بسيط - Simple Click</div>
                </div>
                <div class="image-container click-rotate">
                    <img src="https://picsum.photos/400/250?random=23" alt="Click Rotate">
                    <div class="effect-label">نقر مع دوران - Click Rotate</div>
                </div>
                <div class="image-container click-fade">
                    <img src="https://picsum.photos/400/250?random=24" alt="Click Fade">
                    <div class="effect-label">نقر مع تلاشي - Click Fade</div>
                </div>
            </div>
        </div>
        
        <!-- أزرار التحكم -->
        <div class="controls">
            <button class="demo-button" onclick="animateAll()">🎬 تشغيل جميع التأثيرات</button>
            <button class="demo-button" onclick="resetAll()">🔄 إعادة تعيين</button>
            <button class="demo-button" onclick="toggleEffects()">⏸️ إيقاف/تشغيل</button>
        </div>
        
        <!-- أمثلة الكود -->
        <div class="section">
            <h2>💻 أمثلة الكود - Code Examples</h2>
            
            <div class="info-box">
                <h3>🔧 استخدام GSAP</h3>
                <p>للاستخدام في مشروعك، قم بإضافة الكلاسات التالية للصور:</p>
            </div>
            
            <div class="code-example">
<pre><code>&lt;!-- تكبير عند التمرير --&gt;
&lt;div class="image-container zoom-on-scroll"&gt;
    &lt;img src="image.jpg" alt="Image"&gt;
&lt;/div&gt;

&lt;!-- تحريك أفقي --&gt;
&lt;div class="image-container pan-horizontal"&gt;
    &lt;img src="image.jpg" alt="Image"&gt;
&lt;/div&gt;

&lt;!-- Parallax --&gt;
&lt;div class="image-container parallax-simple"&gt;
    &lt;img src="image.jpg" alt="Image"&gt;
&lt;/div&gt;

&lt;!-- تلاشي من الأعلى --&gt;
&lt;div class="image-container fade-from-top"&gt;
    &lt;img src="image.jpg" alt="Image"&gt;
&lt;/div&gt;

&lt;!-- دوران بسيط --&gt;
&lt;div class="image-container rotate-simple"&gt;
    &lt;img src="image.jpg" alt="Image"&gt;
&lt;/div&gt;

&lt;!-- تأثير 2.5D --&gt;
&lt;div class="image-container effect-2-5d-simple"&gt;
    &lt;img src="image.jpg" alt="Image"&gt;
&lt;/div&gt;</code></pre>
            </div>
            
            <div class="info-box">
                <h3>📱 استخدام JavaScript</h3>
                <p>للاستخدام المتقدم، يمكنك استخدام JavaScript:</p>
            </div>
            
            <div class="code-example">
<pre><code>// استيراد المكتبة
import ImageEffects from './animations/image-effects.js';

// إنشاء مثيل
const imageEffects = new ImageEffects();

// تطبيق تأثير على عنصر
const element = document.querySelector('.my-image');
imageEffects.applyEffect(element, 'zoom', {
    duration: 1.5,
    ease: 'power2.out',
    delay: 0.5
});

// إزالة جميع التأثيرات
imageEffects.removeAllEffects();

// إعادة تعيين جميع العناصر
imageEffects.resetAllElements();</code></pre>
            </div>
        </div>
    </div>
    
    <!-- GSAP CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    
    <!-- JavaScript الخاص بالتأثيرات -->
    <script>
        // تسجيل ScrollTrigger plugin
        gsap.registerPlugin(ScrollTrigger);
        
        // تهيئة التأثيرات
        document.addEventListener('DOMContentLoaded', function() {
            initializeEffects();
        });
        
        function initializeEffects() {
            // تكبير عند التمرير
            gsap.utils.toArray('.zoom-on-scroll').forEach(element => {
                gsap.fromTo(element, {
                    scale: 0.8,
                    opacity: 0.7
                }, {
                    scale: 1,
                    opacity: 1,
                    duration: 1.5,
                    ease: "power2.out",
                    scrollTrigger: {
                        trigger: element,
                        start: "top 80%",
                        end: "bottom 20%",
                        toggleActions: "play none none reverse"
                    }
                });
            });
            
            // تحريك أفقي
            gsap.utils.toArray('.pan-horizontal').forEach(element => {
                gsap.fromTo(element, {
                    x: -100,
                    opacity: 0
                }, {
                    x: 0,
                    opacity: 1,
                    duration: 1,
                    ease: "power2.out",
                    scrollTrigger: {
                        trigger: element,
                        start: "top 80%",
                        toggleActions: "play none none reverse"
                    }
                });
            });
            
            // تحريك عمودي
            gsap.utils.toArray('.pan-vertical').forEach(element => {
                gsap.fromTo(element, {
                    y: 100,
                    opacity: 0
                }, {
                    y: 0,
                    opacity: 1,
                    duration: 1,
                    ease: "power2.out",
                    scrollTrigger: {
                        trigger: element,
                        start: "top 80%",
                        toggleActions: "play none none reverse"
                    }
                });
            });
            
            // تحريك قطري
            gsap.utils.toArray('.pan-diagonal').forEach(element => {
                gsap.fromTo(element, {
                    x: -100,
                    y: 100,
                    opacity: 0
                }, {
                    x: 0,
                    y: 0,
                    opacity: 1,
                    duration: 1.2,
                    ease: "power2.out",
                    scrollTrigger: {
                        trigger: element,
                        start: "top 80%",
                        toggleActions: "play none none reverse"
                    }
                });
            });
            
            // Parallax
            gsap.utils.toArray('.parallax-simple').forEach(element => {
                gsap.to(element, {
                    y: -100,
                    ease: "none",
                    scrollTrigger: {
                        trigger: element,
                        start: "top bottom",
                        end: "bottom top",
                        scrub: true
                    }
                });
            });
            
            gsap.utils.toArray('.parallax-medium').forEach(element => {
                gsap.to(element, {
                    y: -200,
                    ease: "none",
                    scrollTrigger: {
                        trigger: element,
                        start: "top bottom",
                        end: "bottom top",
                        scrub: true
                    }
                });
            });
            
            gsap.utils.toArray('.parallax-strong').forEach(element => {
                gsap.to(element, {
                    y: -300,
                    ease: "none",
                    scrollTrigger: {
                        trigger: element,
                        start: "top bottom",
                        end: "bottom top",
                        scrub: true
                    }
                });
            });
            
            // تلاشي بسيط
            gsap.utils.toArray('.fade-simple').forEach(element => {
                gsap.fromTo(element, {
                    opacity: 0
                }, {
                    opacity: 1,
                    duration: 1,
                    ease: "power2.out",
                    scrollTrigger: {
                        trigger: element,
                        start: "top 80%",
                        toggleActions: "play none none reverse"
                    }
                });
            });
            
            // تلاشي من الأعلى
            gsap.utils.toArray('.fade-from-top').forEach(element => {
                gsap.fromTo(element, {
                    opacity: 0,
                    y: -50
                }, {
                    opacity: 1,
                    y: 0,
                    duration: 1,
                    ease: "power2.out",
                    scrollTrigger: {
                        trigger: element,
                        start: "top 80%",
                        toggleActions: "play none none reverse"
                    }
                });
            });
            
            // تلاشي من الأسفل
            gsap.utils.toArray('.fade-from-bottom').forEach(element => {
                gsap.fromTo(element, {
                    opacity: 0,
                    y: 50
                }, {
                    opacity: 1,
                    y: 0,
                    duration: 1,
                    ease: "power2.out",
                    scrollTrigger: {
                        trigger: element,
                        start: "top 80%",
                        toggleActions: "play none none reverse"
                    }
                });
            });
            
            // دوران بسيط
            gsap.utils.toArray('.rotate-simple').forEach(element => {
                gsap.fromTo(element, {
                    rotation: -180,
                    opacity: 0
                }, {
                    rotation: 0,
                    opacity: 1,
                    duration: 1.5,
                    ease: "power2.out",
                    scrollTrigger: {
                        trigger: element,
                        start: "top 80%",
                        toggleActions: "play none none reverse"
                    }
                });
            });
            
            // دوران مستمر
            gsap.utils.toArray('.rotate-continuous').forEach(element => {
                gsap.to(element, {
                    rotation: 360,
                    duration: 10,
                    ease: "none",
                    repeat: -1
                });
            });
            
            // دوران ثلاثي الأبعاد
            gsap.utils.toArray('.rotate-3d').forEach(element => {
                gsap.fromTo(element, {
                    rotationX: -90,
                    rotationY: -90,
                    opacity: 0
                }, {
                    rotationX: 0,
                    rotationY: 0,
                    opacity: 1,
                    duration: 2,
                    ease: "power2.out",
                    scrollTrigger: {
                        trigger: element,
                        start: "top 80%",
                        toggleActions: "play none none reverse"
                    }
                });
            });
            
            // تأثير 2.5D بسيط
            gsap.utils.toArray('.effect-2-5d-simple').forEach(element => {
                gsap.fromTo(element, {
                    rotationX: -15,
                    rotationY: -15,
                    z: -100,
                    opacity: 0
                }, {
                    rotationX: 0,
                    rotationY: 0,
                    z: 0,
                    opacity: 1,
                    duration: 1.5,
                    ease: "power2.out",
                    scrollTrigger: {
                        trigger: element,
                        start: "top 80%",
                        toggleActions: "play none none reverse"
                    }
                });
            });
            
            // تأثير 2.5D مع تحريك
            gsap.utils.toArray('.effect-2-5d-move').forEach(element => {
                gsap.fromTo(element, {
                    rotationX: -30,
                    rotationY: -30,
                    z: -200,
                    x: -100,
                    opacity: 0
                }, {
                    rotationX: 0,
                    rotationY: 0,
                    z: 0,
                    x: 0,
                    opacity: 1,
                    duration: 2,
                    ease: "power2.out",
                    scrollTrigger: {
                        trigger: element,
                        start: "top 80%",
                        toggleActions: "play none none reverse"
                    }
                });
            });
            
            // تأثير 2.5D مع تكبير
            gsap.utils.toArray('.effect-2-5d-scale').forEach(element => {
                gsap.fromTo(element, {
                    rotationX: -20,
                    rotationY: -20,
                    scale: 0.5,
                    opacity: 0
                }, {
                    rotationX: 0,
                    rotationY: 0,
                    scale: 1,
                    opacity: 1,
                    duration: 1.8,
                    ease: "power2.out",
                    scrollTrigger: {
                        trigger: element,
                        start: "top 80%",
                        toggleActions: "play none none reverse"
                    }
                });
            });
        }
        
        // تشغيل جميع التأثيرات
        function animateAll() {
            gsap.utils.toArray('.image-container').forEach((element, index) => {
                gsap.fromTo(element, {
                    scale: 0.8,
                    opacity: 0,
                    y: 50
                }, {
                    scale: 1,
                    opacity: 1,
                    y: 0,
                    duration: 1,
                    ease: "power2.out",
                    delay: index * 0.1
                });
            });
        }
        
        // إعادة تعيين جميع التأثيرات
        function resetAll() {
            gsap.set('.image-container', {
                clearProps: "all"
            });
        }
        
        // إيقاف/تشغيل التأثيرات
        let effectsEnabled = true;
        function toggleEffects() {
            if (effectsEnabled) {
                gsap.globalTimeline.pause();
                effectsEnabled = false;
            } else {
                gsap.globalTimeline.resume();
                effectsEnabled = true;
            }
        }
    </script>
</body>
</html>
