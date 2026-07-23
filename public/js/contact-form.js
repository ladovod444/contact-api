const { createApp, ref } = Vue;

createApp({
    template: `
    <form @submit.prevent="submitForm" class="contact-form">
                    <input v-model="name" type="text" placeholder="Ваше имя" required />
                    <input v-model="phone" type="tel" placeholder="+7 (999) 000-00-00" required />
                    <input v-model="email" type="email" placeholder="Email" required />
                    <textarea v-model="comment" placeholder="Комментарий" required></textarea>

                    <button type="submit" :disabled="isLoading">
                        <span v-if="isLoading">Отправка...</span>
                        <span v-else>Отправить</span>
                    </button>

                    <p v-if="statusMessage" :class="isSuccess ? 'success' : 'error'">
                        <span v-text="statusMessage"></span>
                    </p>
                </form>
  `,
    setup() {
        const name = ref('');
        const phone = ref('');
        const email = ref('');
        const comment = ref('');

        const isLoading = ref(false);
        const statusMessage = ref('');
        const isSuccess = ref(false);

        const submitForm = async () => {
            isLoading.value = true;
            statusMessage.value = '';

            try {
                const payload = {
                    name: name.value,
                    phone: phone.value,
                    email: email.value,
                    comment: comment.value
                };

                const response = await fetch('/api/contact', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                if (response.ok) {
                    isSuccess.value = true;
                    statusMessage.value = 'Спасибо! Сообщение отправлено.';
                    name.value = ''; phone.value = ''; email.value = ''; comment.value = '';
                } else {
                    const data = await response.json().catch(() => ({}));
                    isSuccess.value = false;
                    statusMessage.value = data.message || 'Ошибка при отправке.';
                }
            } catch (error) {
                isSuccess.value = false;
                statusMessage.value = 'Ошибка сети.';
            } finally {
                isLoading.value = false;
            }
        };

        return { name, phone, email, comment, isLoading, statusMessage, isSuccess, submitForm };
    }
}).mount('#app');
