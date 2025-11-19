<template>
    <Head title="Về chúng tôi" />

    <ClientHeaderLayout>
        <div class="bg-primary-50">
            <motion.section
                :initial="sectionMotion.initial"
                :while-in-view="sectionMotion.visible"
                :viewport="sectionMotion.viewport"
                :transition="getSectionTransition(0)"
            >
                <AboutHero :content="heroContent" :highlights="heroHighlights" :service-pills="servicePills" />
            </motion.section>
            <motion.section
                :initial="sectionMotion.initial"
                :while-in-view="sectionMotion.visible"
                :viewport="sectionMotion.viewport"
                :transition="getSectionTransition(1)"
            >
                <AboutOverview :sections="overviewSections" />
            </motion.section>
            <motion.section
                :initial="sectionMotion.initial"
                :while-in-view="sectionMotion.visible"
                :viewport="sectionMotion.viewport"
                :transition="getSectionTransition(2)"
            >
                <AboutAudience :audiences="audiences" />
            </motion.section>
            <motion.section
                :initial="sectionMotion.initial"
                :while-in-view="sectionMotion.visible"
                :viewport="sectionMotion.viewport"
                :transition="getSectionTransition(3)"
            >
                <AboutTalentShowcase :categories="talentCategories" />
            </motion.section>
            <motion.section
                :initial="sectionMotion.initial"
                :while-in-view="sectionMotion.visible"
                :viewport="sectionMotion.viewport"
                :transition="getSectionTransition(4)"
            >
                <AboutVisionMission :vision="vision" :mission-points="missionPoints" />
            </motion.section>
            <motion.section
                :initial="sectionMotion.initial"
                :while-in-view="sectionMotion.visible"
                :viewport="sectionMotion.viewport"
                :transition="getSectionTransition(5)"
            >
                <AboutMission :principles="operatingPrinciples" :commitments="platformCommitments" />
            </motion.section>
            <motion.section
                :initial="sectionMotion.initial"
                :while-in-view="sectionMotion.visible"
                :viewport="sectionMotion.viewport"
                :transition="getSectionTransition(6)"
            >
                <AboutValues :values="coreValues" />
            </motion.section>
            <motion.section
                :initial="sectionMotion.initial"
                :while-in-view="sectionMotion.visible"
                :viewport="sectionMotion.viewport"
                :transition="getSectionTransition(7)"
            >
                <AboutStats :stats="stats" />
            </motion.section>
            <motion.section
                :initial="sectionMotion.initial"
                :while-in-view="sectionMotion.visible"
                :viewport="sectionMotion.viewport"
                :transition="getSectionTransition(8)"
            >
                <AboutTimeline :milestones="timeline" />
            </motion.section>
            <motion.section
                :initial="sectionMotion.initial"
                :while-in-view="sectionMotion.visible"
                :viewport="sectionMotion.viewport"
                :transition="getSectionTransition(9)"
            >
                <AboutTeam :members="teamMembers" />
            </motion.section>
        </div>
    </ClientHeaderLayout>
</template>

<script setup lang="ts">
import { motion } from 'motion-v';
import { Head } from '@inertiajs/vue3';

import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';

import AboutAudience from './components/AboutAudience.vue';
import AboutHero from './components/AboutHero.vue';
import AboutMission from './components/AboutMission.vue';
import AboutOverview from './components/AboutOverview.vue';
import AboutStats from './components/AboutStats.vue';
import AboutTalentShowcase from './components/AboutTalentShowcase.vue';
import AboutTeam from './components/AboutTeam.vue';
import AboutTimeline from './components/AboutTimeline.vue';
import AboutValues from './components/AboutValues.vue';
import AboutVisionMission from './components/AboutVisionMission.vue';

interface StatItem {
    label: string;
    value: string;
    subtext: string;
}

interface Milestone {
    year: string;
    title: string;
    description: string;
}

interface TeamMember {
    name: string;
    role: string;
    description: string;
}

const sectionMotion = {
    initial: { opacity: 0, y: 40 },
    visible: { opacity: 1, y: 0 },
    viewport: { once: true, amount: 0.2 },
} as const;

const getSectionTransition = (index: number) => ({
    duration: 0.6,
    ease: 'easeOut',
    delay: Math.min(index * 0.08, 0.4),
});

const heroContent = {
    eyebrow: 'Sàn TMĐT nhân sự sự kiện',
    title: 'Đặt lịch tổ chức sự kiện',
    description:
        'Sukientot.com là nền tảng kết nối khách hàng có nhu cầu tổ chức sự kiện với đội ngũ nhân sự biểu diễn chuyên nghiệp. Từ đặt lịch, kết nối, vận hành đến đánh giá đều diễn ra trên một giao diện thân thiện, tươi vui và chuẩn mực.',
    subheading: 'Danh mục nổi bật',
};

const servicePills = [
    { label: 'Chú hề đa năng' },
    { label: 'MC hoạt náo' },
    { label: 'Ảo thuật gia' },
    { label: 'Nghệ nhân truyền thống' },
    { label: 'Mascot & trình diễn sân khấu' },
    { label: 'Workshop thiếu nhi' },
];

const heroHighlights = [
    {
        kicker: 'Đặt lịch tức thì',
        title: '30 giây để gửi yêu cầu',
        description: 'Mô hình đặt lịch tương tự ứng dụng on-demand, giúp khách hàng chốt nhân sự nhanh và chính xác.',
    },
    {
        kicker: 'Sàn trung gian',
        title: 'Kết nối minh bạch',
        description: 'Website đóng vai trò trung gian đảm bảo mọi thông tin, chi phí và đánh giá đều rõ ràng giữa hai bên.',
    },
    {
        kicker: 'Nhân sự chuyên nghiệp',
        title: 'Hơn 120+ nghệ sĩ',
        description: 'Từ chú hề, MC, mascot đến nghệ nhân dân gian đều trải qua quy trình duyệt hồ sơ và đào tạo.',
    },
    {
        kicker: 'Vận hành trọn gói',
        title: 'Admin đồng hành 24/7',
        description: 'Đội ngũ vận hành hỗ trợ phân phối đơn hàng, giám sát chất lượng và xử lý khiếu nại trong 24–48h.',
    },
];

const overviewSections = [
    {
        eyebrow: 'Giới thiệu tổng quan',
        title: 'Sàn thương mại điện tử dành riêng cho nhân sự biểu diễn',
        description:
            'Sukientot.com giúp khách hàng tìm kiếm, đặt lịch và theo dõi nhân sự biểu diễn cho mọi dịp từ sinh nhật, khai trương tới lễ hội cộng đồng.',
        points: ['Trung gian hỗ trợ đặt lịch – kết nối – vận hành – đánh giá.', 'Ứng dụng công nghệ để chuẩn hóa quy trình.'],
    },
    {
        eyebrow: 'Trải nghiệm landing page',
        title: 'Ấn tượng đầu tiên cho khách hàng tổ chức sự kiện',
        description:
            'Giao diện tươi vui – chuyên nghiệp – thân thiện với tông xanh biển và trắng, đặt trọng tâm vào banner giới thiệu, đăng ký nhanh và các nút khám phá nhân sự.',
        points: ['Thông điệp rõ ràng: “Đặt chú hề – ảo thuật – MC chỉ trong 30 giây”.', 'Hiển thị ngay nút đăng nhập/đăng ký và danh mục nhân sự.'],
    },
];

const audiences = [
    {
        category: 'Khách hàng',
        title: 'Doanh nghiệp, trường học, nhà hàng, gia đình',
        description:
            'Bất kỳ ai cần thuê nhân sự biểu diễn hoặc hoạt náo cho các dịp sinh nhật, khai trương, lễ hội đều có thể đặt lịch dễ dàng.',
        benefits: ['Tìm đúng nhân sự theo chủ đề sự kiện.', 'Có báo giá rõ ràng và đánh giá minh bạch.', 'Theo dõi tiến độ và chất lượng trên một bảng điều khiển.'],
    },
    {
        category: 'Nhà cung cấp / Nhân sự',
        title: 'Chú hề, MC, nghệ sĩ, nghệ nhân',
        description:
            'Các cá nhân hoặc tổ chức có kỹ năng biểu diễn, hoạt náo, nghệ thuật dân gian có thêm sân chơi chuyên nghiệp và nguồn thu ổn định.',
        benefits: ['Hồ sơ được duyệt và đào tạo bài bản.', 'Chủ động nhận show phù hợp năng lực.', 'Được hỗ trợ xử lý sự cố và thanh toán an toàn.'],
    },
    {
        category: 'Admin',
        title: 'Đội ngũ vận hành sàn',
        description:
            'Quản trị viên điều phối đơn hàng, giám sát hoạt động và hỗ trợ cả khách hàng lẫn nhân sự nhằm đảm bảo trải nghiệm liền mạch.',
        benefits: ['Phân phối nhân sự đúng thời gian, địa điểm.', 'Giải quyết tranh chấp trong 24–48h.', 'Cập nhật chính sách và thông báo kịp thời.'],
    },
];

const talentCategories = [
    {
        title: 'Chú hề & hoạt náo',
        description: 'Đa dạng từ vặn bóng, làm kẹo bông, MC chú hề đến hoạt náo đa năng.',
        tags: ['Chú hề vặn bóng', 'Chú hề đa năng', 'Hoạt náo thiếu nhi'],
    },
    {
        title: 'MC & mascot',
        description: 'Những gương mặt dẫn chương trình linh hoạt trong nhiều concept trang phục.',
        tags: ['MC vest', 'MC chú hề', 'Mascot gấu/robot'],
    },
    {
        title: 'Ảo thuật & xiếc',
        description: 'Từ ảo thuật cận cảnh đến biểu diễn sân khấu hiện đại khiến sự kiện bùng nổ.',
        tags: ['Ảo thuật gia', 'Xiếc thăng bằng', 'Robot LED'],
    },
    {
        title: 'Nghệ nhân truyền thống',
        description: 'Lưu giữ văn hoá qua tò he, lá dừa, vẽ mặt, ông đồ thư pháp.',
        tags: ['Tò he', 'Lá dừa', 'Ông đồ'],
    },
    {
        title: 'Trình diễn dân gian & sân khấu',
        description: 'Múa lân – rồng, bong bóng xà phòng, trình diễn ánh sáng đặc sắc.',
        tags: ['Múa lân', 'Múa rồng', 'Bong bóng khổng lồ'],
    },
    {
        title: 'Workshop & PG',
        description: 'Các hoạt động tô tượng, tranh cát, vòng tay cùng đội PG chuyên nghiệp.',
        tags: ['Workshop tô tượng', 'Tranh cát', 'PG tone lịch sự'],
    },
];

const vision = 'Trở thành nền tảng số 1 Việt Nam cung cấp nhân sự biểu diễn & nghệ thuật sự kiện.';

const missionPoints = [
    'Hỗ trợ hàng ngàn nhân sự biểu diễn có thêm show, tăng thu nhập và phát triển kỹ năng.',
    'Giúp khách hàng đặt dịch vụ nhanh chóng, an tâm, rõ ràng và đúng giá trị.',
    'Xây dựng cộng đồng sự kiện chuyên nghiệp, kết nối bền vững và truyền cảm hứng.',
];

const operatingPrinciples = [
    'Mọi cá nhân/tổ chức tham gia phải đăng ký tài khoản và cung cấp thông tin chính xác, minh bạch.',
    'Website đóng vai trò trung gian hỗ trợ kết nối và không chịu trách nhiệm dân sự khi có vi phạm từ người dùng nhưng luôn hỗ trợ xử lý công bằng.',
    'Sukientot.com có quyền thay đổi nội dung, chính sách, quy định sử dụng và thông báo công khai đến người dùng.',
];

const platformCommitments = [
    'Bảo mật thông tin cá nhân, không chia sẻ cho bên thứ ba khi chưa được phép.',
    'Tạo môi trường làm việc công bằng – chuyên nghiệp – hỗ trợ – uy tín giữa các bên.',
    'Tuân thủ pháp luật Việt Nam, các quy định về hợp đồng dịch vụ, thuế và sở hữu trí tuệ.',
    'Xử lý khiếu nại, tranh chấp minh bạch trong vòng 24–48 giờ kể từ khi tiếp nhận.',
];

const coreValues = [
    {
        title: 'Chuyên nghiệp',
        description: 'Từng bước thao tác – quy trình đặt – phản hồi – báo cáo đều rõ ràng, có chuẩn đo lường.',
    },
    {
        title: 'Trung thực',
        description: 'Minh bạch thông tin, giá cả, đánh giá và cách xử lý sự cố để tạo dựng niềm tin.',
    },
    {
        title: 'Hỗ trợ',
        description: 'Đội ngũ admin trực 24/7, đào tạo và hỗ trợ cả khách hàng lẫn nhân sự.',
    },
    {
        title: 'Phát triển bền vững',
        description: 'Tạo cơ hội nghề nghiệp, tăng kỹ năng và hướng tới cộng đồng sáng tạo lâu dài.',
    },
];

const stats: StatItem[] = [
    { label: 'Đối tác tin cậy', value: '120+', subtext: 'nhân sự/đơn vị biểu diễn' },
    { label: 'Sự kiện đã triển khai', value: '450+', subtext: 'từ 2019 đến nay' },
    { label: 'Tỉ lệ hài lòng', value: '98%', subtext: 'khách hàng doanh nghiệp & cá nhân' },
    { label: 'Địa phương phủ sóng', value: '18', subtext: 'tỉnh/thành' },
];

const timeline: Milestone[] = [
    {
        year: '2019',
        title: 'Ý tưởng ra đời',
        description: 'Khởi động platform beta giúp khách hàng tìm địa điểm và nhân sự biểu diễn phù hợp.',
    },
    {
        year: '2021',
        title: 'Mở rộng kho nhân sự',
        description: 'Kết nối thêm mascot, workshop và nghệ nhân truyền thống trên khắp cả nước.',
    },
    {
        year: '2023',
        title: 'Chuẩn hóa vận hành',
        description: 'Tích hợp quy trình đặt lịch, báo giá, thanh toán và đánh giá ngay trên nền tảng.',
    },
    {
        year: '2024',
        title: 'Định vị landing đầu tiên',
        description: 'Ra mắt trang giới thiệu mới với thông điệp “Đặt chú hề – ảo thuật – MC chỉ trong 30 giây”.',
    },
];

const teamMembers: TeamMember[] = [
    {
        name: 'Nguyễn Văn A',
        role: 'CEO & Co-founder',
        description: '15 năm kinh nghiệm tư vấn giải pháp event cho doanh nghiệp F&B và bán lẻ.',
    },
    {
        name: 'Nguyễn Văn B',
        role: 'Head of Operations',
        description: 'Người đứng sau hệ thống quy trình giúp đội ngũ nhân sự phối hợp trơn tru.',
    },
    {
        name: 'Nguyễn Văn C',
        role: 'Lead Product Designer',
        description: 'Thiết kế trải nghiệm liền mạch dựa trên insight thực tế từ khách hàng.',
    },
    {
        name: 'Nguyễn Văn D',
        role: 'Partnership Manager',
        description: 'Xây dựng mạng lưới nhân sự chất lượng thông qua chính sách minh bạch.',
    },
];
</script>
