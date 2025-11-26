<template>

    <Head title="Hướng dẫn sử dụng" />
    <ClientAppHeaderLayout>
        <TutorialHero title="Hướng dẫn sử dụng dịch vụ"
            description="Chào mừng bạn đến với Sukientot. Dưới đây là quy trình chi tiết để bạn có thể bắt đầu sử dụng dịch vụ của chúng tôi một cách dễ dàng." />

        <div class="bg-gray-50 pb-24">
            <TutorialStep v-for="(step, index) in steps" :key="index" :step-number="index + 1" :title="step.title"
                :description="step.description" :image="step.image" :is-reversed="index % 2 !== 0" />
        </div>

        <RelatedTutorials />
    </ClientAppHeaderLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ClientAppHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';
import TutorialHero from './components/TutorialHero.vue';
import TutorialStep from './components/TutorialStep.vue';
import RelatedTutorials from './components/RelatedTutorials.vue';

const steps = [
    {
        title: 'Đăng ký tài khoản',
        description: 'Bắt đầu bằng việc tạo một tài khoản mới. Bạn có thể đăng ký bằng email hoặc số điện thoại. Đừng quên xác thực tài khoản để bảo mật thông tin.',
        image: 'https://placehold.co/800x600?text=Dang+Ky+Tai+Khoan',
    },
    {
        title: 'Đặt hàng dịch vụ',
        description: 'Tìm kiếm và lựa chọn dịch vụ bạn cần. Chúng tôi cung cấp đa dạng các dịch vụ từ tổ chức sự kiện, thuê thiết bị đến nhân sự.',
        image: 'https://placehold.co/800x600?text=Dat+Hang',
    },
    {
        title: 'Nhập mã giảm giá',
        description: 'Nếu bạn có mã giảm giá, hãy nhập vào bước thanh toán để nhận ưu đãi hấp dẫn từ chúng tôi.',
        image: 'https://placehold.co/800x600?text=Nhap+Ma+Giam+Gia',
    },
    {
        title: 'Chờ đối tác xác nhận',
        description: 'Sau khi đặt hàng, yêu cầu của bạn sẽ được gửi đến đối tác. Vui lòng chờ trong giây lát để đối tác xác nhận đơn hàng.',
        image: 'https://placehold.co/800x600?text=Cho+Xac+Nhan',
    },
    {
        title: 'Xác nhận đơn hàng',
        description: 'Khi đối tác chấp nhận, bạn cần xác nhận lại đơn hàng một lần nữa để đảm bảo mọi thông tin đều chính xác.',
        image: 'https://placehold.co/800x600?text=Xac+Nhan+Don+Hang',
    },
    {
        title: 'Kiểm tra trạng thái',
        description: 'Bạn có thể theo dõi trạng thái đơn hàng của mình bất cứ lúc nào trong phần quản lý đơn hàng.',
        image: 'https://placehold.co/800x600?text=Kiem+Tra+Trang+Thai',
    },
    {
        title: 'Trò chuyện với đối tác',
        description: 'Sử dụng tính năng chat để trao đổi trực tiếp với đối tác về các yêu cầu cụ thể hoặc thay đổi nếu có.',
        image: 'https://placehold.co/800x600?text=Chat+Voi+Doi+Tac',
    },
    {
        title: 'Hoàn thành đơn hàng',
        description: 'Sau khi sử dụng dịch vụ, hãy xác nhận hoàn thành và đừng quên để lại đánh giá để giúp chúng tôi cải thiện chất lượng.',
        image: 'https://placehold.co/800x600?text=Hoan+Thanh',
    },
];
</script>
