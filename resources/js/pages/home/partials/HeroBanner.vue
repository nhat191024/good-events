<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Search, MapPin } from 'lucide-vue-next';
</script>

<template>
    <section class="relative">
        <!-- Gradient Background -->
        <div 
            class="absolute inset-0 z-0"
            style="background: linear-gradient(180deg, var(--token-3d956cea-dbc1-4944-ad45-eefce4d545dc, #f28f8f) 0%, var(--token-3d956cea-dbc1-4944-ad45-eefce4d545dc, rgb(242, 143, 143)) 51.50151983037725%, rgba(242, 143, 143, 0) 100%);"
        ></div>
        
        <div class="relative z-10 container mx-auto px-4 py-16">
            <div class="text-center mb-8">
                <h1 class="text-[25px] font-lexend font-medium md:text-[36px] text-black mb-4">
                    Thuê đối tác xịn. Sự kiện thêm vui
                </h1>
            </div>
            
            <!-- Hero Image/Video -->
            <div class="mb-8 flex justify-center">
                <div class="w-full max-w-4xl h-64 bg-white rounded-lg shadow-lg overflow-hidden">
                    <img 
                        src="/placeholder.svg?height=256&width=896"
                        alt="Hero Banner"
                        class="w-full h-full object-cover"
                    />
                </div>
            </div>
            
            <!-- Search Bar -->
            <div class="max-w-5xl mx-auto">
                <div class="bg-white rounded-lg shadow-lg p-2">
                    <div class="flex gap-4">
                        <div class="flex-1 relative">
                            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                            <Input 
                                placeholder="Tìm dối tác"
                                class="pl-10 border-0 "
                            />
                        </div>
                        <div class="flex items-center gap-2">
                            <MapPin class="text-gray-400 w-5 h-5" />
                            <select class="border-none focus:ring-0 text-gray-600">
                                <option>Toàn Quốc</option>
                                <option>Hà Nội</option>
                                <option>TP.HCM</option>
                            </select>
                        </div>
                        <Button class="bg-red-500 hover:bg-red-600 px-8">
                            Tìm kiếm
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
