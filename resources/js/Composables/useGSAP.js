import { onUnmounted } from 'vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

export function useGSAP() {
    const ctx = gsap.context(() => {});

    onUnmounted(() => {
        ctx.revert(); // Context cleanup for all animations inside
    });

    return {
        gsap,
        ScrollTrigger,
        ctx
    };
}
