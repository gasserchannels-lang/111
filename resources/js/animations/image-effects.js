/**
 * GSAP Image Effects Library
 * مكتبة تأثيرات الصور باستخدام GSAP
 * 
 * هذا الملف يحتوي على جميع تأثيرات الصور المتحركة
 * Zoom, Pan, Parallax, Fade, Rotation, 2.5D
 */

// استيراد GSAP
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

// تسجيل ScrollTrigger plugin
gsap.registerPlugin(ScrollTrigger);

/**
 * فئة تأثيرات الصور
 * Image Effects Class
 */
class ImageEffects {
    constructor() {
        this.init();
    }

    /**
     * تهيئة التأثيرات
     * Initialize effects
     */
    init() {
        this.setupZoomEffects();
        this.setupPanEffects();
        this.setupParallaxEffects();
        this.setupFadeEffects();
        this.setupRotationEffects();
        this.setup2_5DEffects();
        this.setupHoverEffects();
        this.setupClickEffects();
    }

    /**
     * تأثيرات التكبير
     * Zoom Effects
     */
    setupZoomEffects() {
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

        // تكبير عند التمرير فوق
        gsap.utils.toArray('.zoom-on-hover').forEach(element => {
            element.addEventListener('mouseenter', () => {
                gsap.to(element, {
                    scale: 1.1,
                    duration: 0.3,
                    ease: "power2.out"
                });
            });

            element.addEventListener('mouseleave', () => {
                gsap.to(element, {
                    scale: 1,
                    duration: 0.3,
                    ease: "power2.out"
                });
            });
        });

        // تكبير تدريجي
        gsap.utils.toArray('.zoom-progressive').forEach(element => {
            gsap.fromTo(element, {
                scale: 0.5
            }, {
                scale: 1,
                duration: 2,
                ease: "elastic.out(1, 0.3)",
                scrollTrigger: {
                    trigger: element,
                    start: "top 90%",
                    toggleActions: "play none none reverse"
                }
            });
        });
    }

    /**
     * تأثيرات التحريك
     * Pan Effects
     */
    setupPanEffects() {
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
    }

    /**
     * تأثيرات Parallax
     * Parallax Effects
     */
    setupParallaxEffects() {
        // Parallax بسيط
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

        // Parallax معتدل
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

        // Parallax قوي
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

        // Parallax مع دوران
        gsap.utils.toArray('.parallax-rotate').forEach(element => {
            gsap.to(element, {
                y: -150,
                rotation: 5,
                ease: "none",
                scrollTrigger: {
                    trigger: element,
                    start: "top bottom",
                    end: "bottom top",
                    scrub: true
                }
            });
        });
    }

    /**
     * تأثيرات التلاشي
     * Fade Effects
     */
    setupFadeEffects() {
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

        // تلاشي من اليسار
        gsap.utils.toArray('.fade-from-left').forEach(element => {
            gsap.fromTo(element, {
                opacity: 0,
                x: -50
            }, {
                opacity: 1,
                x: 0,
                duration: 1,
                ease: "power2.out",
                scrollTrigger: {
                    trigger: element,
                    start: "top 80%",
                    toggleActions: "play none none reverse"
                }
            });
        });

        // تلاشي من اليمين
        gsap.utils.toArray('.fade-from-right').forEach(element => {
            gsap.fromTo(element, {
                opacity: 0,
                x: 50
            }, {
                opacity: 1,
                x: 0,
                duration: 1,
                ease: "power2.out",
                scrollTrigger: {
                    trigger: element,
                    start: "top 80%",
                    toggleActions: "play none none reverse"
                }
            });
        });
    }

    /**
     * تأثيرات الدوران
     * Rotation Effects
     */
    setupRotationEffects() {
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

        // دوران عند التمرير
        gsap.utils.toArray('.rotate-on-scroll').forEach(element => {
            gsap.to(element, {
                rotation: 360,
                ease: "none",
                scrollTrigger: {
                    trigger: element,
                    start: "top bottom",
                    end: "bottom top",
                    scrub: true
                }
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
    }

    /**
     * تأثيرات 2.5D
     * 2.5D Effects
     */
    setup2_5DEffects() {
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

    /**
     * تأثيرات التمرير
     * Hover Effects
     */
    setupHoverEffects() {
        // تأثير التمرير البسيط
        gsap.utils.toArray('.hover-simple').forEach(element => {
            element.addEventListener('mouseenter', () => {
                gsap.to(element, {
                    scale: 1.05,
                    duration: 0.3,
                    ease: "power2.out"
                });
            });

            element.addEventListener('mouseleave', () => {
                gsap.to(element, {
                    scale: 1,
                    duration: 0.3,
                    ease: "power2.out"
                });
            });
        });

        // تأثير التمرير مع دوران
        gsap.utils.toArray('.hover-rotate').forEach(element => {
            element.addEventListener('mouseenter', () => {
                gsap.to(element, {
                    rotation: 5,
                    scale: 1.1,
                    duration: 0.3,
                    ease: "power2.out"
                });
            });

            element.addEventListener('mouseleave', () => {
                gsap.to(element, {
                    rotation: 0,
                    scale: 1,
                    duration: 0.3,
                    ease: "power2.out"
                });
            });
        });

        // تأثير التمرير مع تلاشي
        gsap.utils.toArray('.hover-fade').forEach(element => {
            element.addEventListener('mouseenter', () => {
                gsap.to(element, {
                    opacity: 0.8,
                    scale: 1.05,
                    duration: 0.3,
                    ease: "power2.out"
                });
            });

            element.addEventListener('mouseleave', () => {
                gsap.to(element, {
                    opacity: 1,
                    scale: 1,
                    duration: 0.3,
                    ease: "power2.out"
                });
            });
        });
    }

    /**
     * تأثيرات النقر
     * Click Effects
     */
    setupClickEffects() {
        // تأثير النقر البسيط
        gsap.utils.toArray('.click-simple').forEach(element => {
            element.addEventListener('click', () => {
                gsap.to(element, {
                    scale: 0.95,
                    duration: 0.1,
                    ease: "power2.out",
                    yoyo: true,
                    repeat: 1
                });
            });
        });

        // تأثير النقر مع دوران
        gsap.utils.toArray('.click-rotate').forEach(element => {
            element.addEventListener('click', () => {
                gsap.to(element, {
                    rotation: 360,
                    duration: 0.5,
                    ease: "power2.out"
                });
            });
        });

        // تأثير النقر مع تلاشي
        gsap.utils.toArray('.click-fade').forEach(element => {
            element.addEventListener('click', () => {
                gsap.to(element, {
                    opacity: 0.5,
                    duration: 0.2,
                    ease: "power2.out",
                    yoyo: true,
                    repeat: 1
                });
            });
        });
    }

    /**
     * تطبيق تأثير على عنصر
     * Apply effect to element
     */
    applyEffect(element, effectType, options = {}) {
        const defaultOptions = {
            duration: 1,
            ease: "power2.out",
            delay: 0
        };

        const config = { ...defaultOptions, ...options };

        switch (effectType) {
            case 'zoom':
                gsap.fromTo(element, {
                    scale: 0.8,
                    opacity: 0.7
                }, {
                    scale: 1,
                    opacity: 1,
                    duration: config.duration,
                    ease: config.ease,
                    delay: config.delay
                });
                break;

            case 'fade':
                gsap.fromTo(element, {
                    opacity: 0
                }, {
                    opacity: 1,
                    duration: config.duration,
                    ease: config.ease,
                    delay: config.delay
                });
                break;

            case 'slide-left':
                gsap.fromTo(element, {
                    x: -100,
                    opacity: 0
                }, {
                    x: 0,
                    opacity: 1,
                    duration: config.duration,
                    ease: config.ease,
                    delay: config.delay
                });
                break;

            case 'slide-right':
                gsap.fromTo(element, {
                    x: 100,
                    opacity: 0
                }, {
                    x: 0,
                    opacity: 1,
                    duration: config.duration,
                    ease: config.ease,
                    delay: config.delay
                });
                break;

            case 'slide-up':
                gsap.fromTo(element, {
                    y: 100,
                    opacity: 0
                }, {
                    y: 0,
                    opacity: 1,
                    duration: config.duration,
                    ease: config.ease,
                    delay: config.delay
                });
                break;

            case 'slide-down':
                gsap.fromTo(element, {
                    y: -100,
                    opacity: 0
                }, {
                    y: 0,
                    opacity: 1,
                    duration: config.duration,
                    ease: config.ease,
                    delay: config.delay
                });
                break;

            case 'rotate':
                gsap.fromTo(element, {
                    rotation: -180,
                    opacity: 0
                }, {
                    rotation: 0,
                    opacity: 1,
                    duration: config.duration,
                    ease: config.ease,
                    delay: config.delay
                });
                break;

            case 'scale':
                gsap.fromTo(element, {
                    scale: 0,
                    opacity: 0
                }, {
                    scale: 1,
                    opacity: 1,
                    duration: config.duration,
                    ease: config.ease,
                    delay: config.delay
                });
                break;

            default:
                console.warn(`Unknown effect type: ${effectType}`);
        }
    }

    /**
     * إزالة جميع التأثيرات
     * Remove all effects
     */
    removeAllEffects() {
        gsap.killTweensOf("*");
    }

    /**
     * إعادة تعيين جميع العناصر
     * Reset all elements
     */
    resetAllElements() {
        gsap.set("*", {
            clearProps: "all"
        });
    }
}

// تصدير الفئة
export default ImageEffects;

// تهيئة تلقائية عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', () => {
    new ImageEffects();
});

// استخدام في Node.js
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ImageEffects;
}
