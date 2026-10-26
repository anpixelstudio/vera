import { router } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import { toast } from 'sonner';
import type { FlashToast } from '@/types/ui';

function showToast(data: FlashToast | undefined): void {
    if (!data?.type || !data.message) {
        return;
    }

    toast[data.type](data.message);
}

/**
 * Toaster sits beside the Inertia <App /> via withApp(), so this hook must
 * not call usePage() (no page context). Use router flash events + URL flag.
 */
export function useFlashToast(): void {
    const seenVerified = useRef(false);

    useEffect(() => {
        return router.on('flash', (event) => {
            const flash = (event as CustomEvent).detail?.flash;
            showToast(flash?.toast as FlashToast | undefined);
        });
    }, []);

    useEffect(() => {
        if (seenVerified.current) {
            return;
        }

        const params = new URLSearchParams(window.location.search);
        if (params.get('verified') === '1') {
            seenVerified.current = true;
            toast.success('Email confirmed. Your account is ready.');
            params.delete('verified');
            const next = `${window.location.pathname}${params.toString() ? `?${params}` : ''}${window.location.hash}`;
            window.history.replaceState({}, '', next);
        }
    }, []);
}
