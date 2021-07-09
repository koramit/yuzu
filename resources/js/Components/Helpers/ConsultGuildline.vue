<template>
    <div
        v-if="!$page.props.flash.messages"
        class="flex items-center rounded-tl-lg rounded-tr-lg border-alt-theme-light border-8 border-t-0 border-l-0 border-r-0 shadow p-4"
    >
        <icon
            class="block w-12 h-12 text-alt-theme-light"
            name="info-circle"
        />
        <div class="ml-4 text-dark-theme-light text-sm font-normal">
            <div class="flex my-2 ">
                <p>๏</p>
                <p class="px-2 tracking-wide leading-5 font-semibold">
                    เกณฑ์พิจารณาปรึกษาแพทย์ประจำหอผู้ป่วยเฉพาะกิจ (Hospitel)
                </p>
            </div>

            <div class="flex my-2 mt-4">
                <p class="text-soft-theme-light">
                    ๏
                </p>
                <p class="p-2 tracking-wide leading-5 underline">
                    มีเกณฑ์ข้อใดข้อหนึ่งดังต่อไปนี้
                </p>
            </div>

            <div class="flex my-2">
                <p>1</p>
                <p class="px-2 tracking-wide leading-5">
                    Body temperature > 38 ℃
                </p>
            </div>

            <div class="flex my-2">
                <p>2</p>
                <p class="px-2 tracking-wide leading-5">
                    Oxygen saturation room air ＜ 96% at rest
                </p>
            </div>

            <div class="flex my-2">
                <p>3</p>
                <p class="px-2 tracking-wide leading-5">
                    มีอาการทางคลินิกแย่ลง เช่น ไข้สูง เหนื่อยมากขึ้น
                </p>
            </div>

            <div class="flex my-2">
                <p class="text-soft-theme-light">
                    ๏
                </p>
                <p class="p-2 tracking-wide leading-5 italic">
                    โดยหากแพทย์ประเมินว่าจำเป็นต้องส่งกลับ พิจารณาปรึกษาแพทย์ตามรายชื่อนี้
                </p>
            </div>

            <div class="flex my-2">
                <p class="text-soft-theme-light">
                    ๏
                </p>
                <div>
                    <div class="text-sm font-semibold text-dark-theme-light flex items-center">
                        <p>{{ referer.name }}</p>
                        <button
                            @click="showContact = !showContact"
                            class="ml-4 text-alt-theme-light"
                        >
                            <icon
                                class="w-4 h-4"
                                name="phone-square"
                            />
                        </button>
                    </div>
                    <p
                        class="text-sm font-semibold text-alt-theme-light mt-2"
                        v-if="showContact"
                    >
                        {{ referer.tel_no }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Icon from '@/Components/Helpers/Icon';
export default {
    components: { Icon },
    props: {
        referer: { type: Object, required: true }
    },
    data () {
        return {
            showContact: false
        };
    }
};
</script>