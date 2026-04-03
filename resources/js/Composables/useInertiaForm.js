import { useForm } from '@inertiajs/vue3'
import { useToast } from './useToast'

/**
 * Wrapper de useForm de Inertia con:
 * - Toast automático en errores de servidor
 * - Reset automático configurable post-submit
 * - Helper `hasError(field)` para inputs
 */
export function useInertiaForm(initialValues = {}, options = {}) {
    const toast = useToast()
    const form = useForm(initialValues)

    const {
        resetOnSuccess = false,
        successMessage = null,
        errorMessage = 'Por favor corrige los errores indicados.',
    } = options

    function submit(method, url, hooks = {}) {
        form[method](url, {
            onSuccess: (page) => {
                if (successMessage) toast.success(successMessage)
                if (resetOnSuccess) form.reset()
                hooks.onSuccess?.(page)
            },
            onError: (errors) => {
                toast.error(errorMessage)
                hooks.onError?.(errors)
            },
            onFinish: () => {
                hooks.onFinish?.()
            },
            ...hooks,
        })
    }

    function hasError(field) {
        return !!form.errors[field]
    }

    function clearError(field) {
        form.clearErrors(field)
    }

    return { form, submit, hasError, clearError }
}
