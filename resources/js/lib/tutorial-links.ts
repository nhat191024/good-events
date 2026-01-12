import { buildTutorialLink } from './tutorial-helper';

export const tutorialSectionIds = {
    client: {
        fastBookingFlow: 'dang-ky-lam-khach-hang-cua-su-kien-tot-va-dat-mot-don-su-kien-moi-bang-tinh-nang-dat-don-nhanh',
        registerAccount: 'dang-ky-tai-khoan-khach-hang-tren-su-kien-tot',
        quickOrder: 'dat-mot-don-hang-moi-bang-tinh-nang-dat-don-nhanh-va-kiem-tra-danh-sach-don-hang-da-dat',
        trackingFlow: 'toan-bo-luong-theo-doi-va-hoan-thanh-danh-gia-don-voi-vai-tro-khach',
        inspectPartner: 'xem-thong-tin-doi-tac-truoc-khi-chot-don',
        voucherTutorial: 'su-dung-ma-giam-gia-voucher-truoc-khi-chot-doi-tac',
        chatGuide: 'su-dung-tinh-nang-chat-de-nhan-tin-tren-su-kien-tot-khach',
        reportsGuide: 'su-dung-tinh-nang-bao-cao-tren-su-kien-tot-khach',
        reviewStaff: 'danh-gia-nhan-su-don-hang-sau-khi-hoan-thanh-don-tren-su-kien-tot',
        becomePartner: 'tro-thanh-doi-tac-tu-vai-tro-khach-hang-tren-su-kien-tot',
    },
    partner: {
        registerStaffAccount: 'doi-tac-cach-dang-ky-tai-khoan-nhan-su',
        updateStaffInfo: 'doi-tac-cap-nhat-thong-tin-nhan-su',
        loginEvent: 'cach-dang-nhap-tai-khoan-su-kien-tot',
        addServiceVideo: 'doi-tac-them-dich-vu-va-dang-tai-video-dich-vu-va-them-anh-sau-khi-duoc-duyet',
        editServiceMedia: 'doi-tac-them-sua-anh-dich-vu-sau-khi-dich-vu-duoc-duyet',
        postOrderFlow: 'doi-tac-toan-bo-luong-don-hang-sau-khi-da-duoc-khach-chot-don',
        receiveOrders: 'doi-tac-nhan-don-tu-khach-va-doi-duoc-khach-chot-don',
        viewSchedule: 'doi-tac-su-dung-tinh-nang-xem-lich-dien-show',
        confirmArrival: 'doi-tac-them-anh-da-den-noi-sau-khi-da-den-noi-to-chuc-su-kien-cua-khach-va-hoan-thanh-don',
        addFunds: 'doi-tac-nap-tien-vao-vi-doi-tac-su-kien-tot',
    },
};

export const tutorialQuickLinks = {
    seeAllTutorials: {
        label: 'Xem tất cả hướng dẫn',
        href: buildTutorialLink('tutorial.index'),
    },

    // client links
    clientRegister: {
        label: 'Đăng ký tài khoản khách hàng',
        href: buildTutorialLink('tutorial.client', tutorialSectionIds.client.registerAccount),
    },
    clientRegisterAndFastBooking: {
        label: 'Đăng ký và đặt show nhanh',
        href: buildTutorialLink('tutorial.client', tutorialSectionIds.client.fastBookingFlow),
    },
    clientVoucher: {
        label: 'Đổi mã giảm giá',
        href: buildTutorialLink('tutorial.client', tutorialSectionIds.client.voucherTutorial),
    },
    clientBecomePartner: {
        label: 'Đăng ký trở thành đối tác',
        href: buildTutorialLink('tutorial.client', tutorialSectionIds.client.becomePartner),
    },
    clientQuickOrder: {
        label: 'Đặt đơn & kiểm tra lịch sử',
        href: buildTutorialLink('tutorial.client', tutorialSectionIds.client.quickOrder),
    },
    clientTrackingFlow: {
        label: 'Sau khi đối tác nhận đơn của bạn thì cần làm gì?',
        href: buildTutorialLink('tutorial.client', tutorialSectionIds.client.trackingFlow),
    },
    clientInspectPartner: {
        label: 'Xem thông tin đối tác trước khi chốt',
        href: buildTutorialLink('tutorial.client', tutorialSectionIds.client.inspectPartner),
    },
    clientChatGuide: {
        label: 'Cách sử dụng tính năng nhắn tin',
        href: buildTutorialLink('tutorial.client', tutorialSectionIds.client.chatGuide),
    },
    clientReportsGuide: {
        label: 'Gửi báo cáo & khiếu nại',
        href: buildTutorialLink('tutorial.client', tutorialSectionIds.client.reportsGuide),
    },
    clientReviewStaff: {
        label: 'Đánh giá nhân sự sau khi hoàn thành đơn',
        href: buildTutorialLink('tutorial.client', tutorialSectionIds.client.reviewStaff),
    },
    // partner links
    partnerRegister: {
        label: 'Đăng ký tài khoản nhân sự đối tác',
        href: buildTutorialLink('tutorial.partner', tutorialSectionIds.partner.registerStaffAccount),
    },
    partnerAddService: {
        label: 'Tải lên ảnh dịch vụ của mình (sau khi đã được duyệt)',
        href: buildTutorialLink('tutorial.partner', tutorialSectionIds.partner.addServiceVideo),
    },
    partnerSchedule: {
        label: 'Xem lịch diễn show',
        href: buildTutorialLink('tutorial.partner', tutorialSectionIds.partner.viewSchedule),
    },
    partnerUpdateStaffInfo: {
        label: 'Cập nhật thông tin nhân sự',
        href: buildTutorialLink('tutorial.partner', tutorialSectionIds.partner.updateStaffInfo),
    },
    partnerLoginEvent: {
        label: 'Đăng nhập sự kiện tốt',
        href: buildTutorialLink('tutorial.partner', tutorialSectionIds.partner.loginEvent),
    },
    partnerEditServiceMedia: {
        label: 'Chỉnh sửa ảnh sau khi đã được duyệt',
        href: buildTutorialLink('tutorial.partner', tutorialSectionIds.partner.editServiceMedia),
    },
    partnerPostOrderFlow: {
        label: 'Những thao tác sau khi được khách chốt đơn',
        href: buildTutorialLink('tutorial.partner', tutorialSectionIds.partner.postOrderFlow),
    },
    partnerReceiveOrders: {
        label: 'Cách nhận đơn & chờ chốt đơn từ khách',
        href: buildTutorialLink('tutorial.partner', tutorialSectionIds.partner.receiveOrders),
    },
    partnerConfirmArrival: {
        label: 'Cách chụp ảnh đã đến nơi & hoàn thành đơn',
        href: buildTutorialLink('tutorial.partner', tutorialSectionIds.partner.confirmArrival),
    },
    partnerAddFunds: {
        label: 'Nạp tiền vào ví đối tác',
        href: buildTutorialLink('tutorial.partner', tutorialSectionIds.partner.addFunds),
    },
};
