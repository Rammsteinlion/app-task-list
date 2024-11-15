<script setup lang="ts">
import { computed, Ref, ref, watch } from 'vue'
import IconLockPopUp from '@/assets/icons/popUp/IconLockPopUp.vue'
import IconUnlockPopUp from '@/assets/icons/popUp/IconUnlockPopUp.vue'

const props = defineProps<{
    defaultRounded?:string
    hexadecimalSelect?:string
}>()
const emits = defineEmits<{
    (e: 'ondatabutton', key: string, color: string): void
    (e: 'onColorChange', key: string, color: string): void;
}>()

//colores
const hue = ref(200);
const alpha = ref(100);
const saturationY = ref(100);
const lightnessX = ref(50);
const colorArea = ref<HTMLDivElement | null>(null);

//config inputs
const isSelected = ref(false);
const isFirstInputEditable = ref(true);


type CornerValues = {
    [key: string]: Ref<string>;
};

const cornerValues: CornerValues = {
    topLeft: ref('0'),
    topRight: ref('0'),
    bottomLeft: ref('0'),
    bottomRight: ref('0')
};

const { topLeft, topRight, bottomLeft, bottomRight } = cornerValues;


const initializeValues = ():void => {
    const values = props.defaultRounded ? props.defaultRounded.split(' ').map(val => val.replace('px', '').trim()) : ['0', '0', '0', '0'];
    topLeft.value = values[0] || '0';
    topRight.value = values[1] || '0';
    bottomLeft.value = values[2] || '0';
    bottomRight.value = values[3] || '0';
};

initializeValues();

// Función para sincronizar los bordes
const syncBorders = ():void => {
    const value = topLeft.value || '0'; // Usar '0' si `topLeft` está vacío
    topRight.value = value;
    bottomLeft.value = value;
    bottomRight.value = value;
};

const toggleSelection = ():void => {
    isSelected.value = !isSelected.value;
    isFirstInputEditable.value = !isSelected.value;

    if (isSelected.value) {
        syncBorders();
    }
    emitRounded();
};

// Función para actualizar los valores de los inputs y emitir cambios
const onInputChange = (corner: string, value: string) => {
    // Evitar caracteres no numéricos
    const validValue = value.replace(/[^0-9]/g, '');
    cornerValues[corner].value = validValue;

    if (isSelected.value) {
        syncBorders();
    }
    emitRounded();
};


const emitRounded = () => {
    const formattedValues = [
        `${topLeft.value || '0'}px`,
        `${topRight.value || '0'}px`,
        `${bottomLeft.value || '0'}px`,
        `${bottomRight.value || '0'}px`
    ].join(' ').trim();

    emits('ondatabutton', 'roundedButton',formattedValues);
};

watch(
    () => props.defaultRounded!,
    (newRounded) => {
        const values = newRounded.split(' ').map(val => val.replace('px', '').trim());
        topLeft.value = values[0] || '0';
        topRight.value = values[1] || '0';
        bottomLeft.value = values[2] || '0';
        bottomRight.value = values[3] || '0';
    }
);

//config colors
const colors = ref<string[]>([
    '#EF4444','#F97316','#FACC15','#4ADE80','#2DD4BF','#6366F1','#EC4899',
    '#F43F5E','#D946EF','#8B5CF6','#0EA5E9','#10B981','#84CC16',
]);

const selectedColor = ref(props.hexadecimalSelect || '')

const handleColorSelect = (color:string):void => {
    selectedColor.value = color
    emits('onColorChange', 'colorButton',color)
}

const addColor = ():void => {
    if (!colors.value.includes(selectedColor.value)) {
        colors.value.push(selectedColor.value) // Agregar el color si no está ya en la lista
    }
    emits('onColorChange', 'colorButton', selectedColor.value) // Emitir el color actualizado
}

// Convierte el color HSLA a hexadecimal y aplica la opacidad en hexadecimal también
const colorHexWithAlpha = computed(() => {
    const hslToRgb = (h: number, s: number, l: number) => {
        l /= 100;
        const a = s * Math.min(l, 1 - l) / 100;
        const f = (n: number) => {
            const k = (n + h / 30) % 12;
            const color = l - a * Math.max(Math.min(k - 3, 9 - k, 1), -1);
            return Math.round(255 * color);
        };
        return [f(0), f(8), f(4)];
    };

    const [r, g, b] = hslToRgb(hue.value, saturationY.value, lightnessX.value);
    const alphaHex = Math.round((alpha.value / 100) * 255)
        .toString(16)
        .padStart(2, '0');

    return `#${((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1)}${alphaHex}`;
});

// Almacena el valor del input hexadecimal
const hexInput = ref('');

watch([hue, saturationY, lightnessX, alpha], () => {
    hexInput.value = colorHexWithAlpha.value;
    selectedColor.value = colorHexWithAlpha.value; // Actualizar selectedColor cuando cambian los controles
    emits('onColorChange', 'colorButton', selectedColor.value); // Emitir el color actualizado
});

// Actualiza el color a partir del valor hexadecimal ingresado
const updateColorFromHex = ():void => {
    const hex = hexInput.value.trim();
    if (/^#[0-9A-Fa-f]{6}(?:[0-9A-Fa-f]{2})?$/.test(hex)) {
        const rgb = hexToRgb(hex);
        if (rgb) {
            const { r, g, b } = rgb;
            const hsl = rgbToHsl(r, g, b);
            if (hsl) {
                hue.value = hsl.h;
                saturationY.value = hsl.s;
                lightnessX.value = hsl.l;
                alpha.value = parseInt(hex.slice(7, 9) || 'FF', 16) / 255 * 100;
            }
        }
    }
};

// Convierte un valor hexadecimal a RGB
const hexToRgb = (hex: string): { r: number, g: number, b: number, a: number } | null => {
    let r: number, g: number, b: number, a: number = 255;
    if (hex.length === 7) {
        r = parseInt(hex.slice(1, 3), 16);
        g = parseInt(hex.slice(3, 5), 16);
        b = parseInt(hex.slice(5, 7), 16);
    } else if (hex.length === 9) {
        r = parseInt(hex.slice(1, 3), 16);
        g = parseInt(hex.slice(3, 5), 16);
        b = parseInt(hex.slice(5, 7), 16);
        a = parseInt(hex.slice(7, 9), 16);
    } else {
        return null; // Return null if hex is invalid
    }
    return { r, g, b, a };
};

// Convierte RGB a HSL
const rgbToHsl = (r: number, g: number, b: number): { h: number, s: number, l: number } | null => {
    r /= 255;
    g /= 255;
    b /= 255;
    let max = Math.max(r, g, b),
        min = Math.min(r, g, b),
        h = 0,
        s = 0,
        l = (max + min) / 2;

    if (max !== min) {
        const d = max - min;
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
        if (max === r) h = (g - b) / d + (g < b ? 6 : 0);
        else if (max === g) h = (b - r) / d + 2;
        else h = (r - g) / d + 4;
        h /= 6;
    }

    return { h: Math.round(h * 360), s: Math.round(s * 100), l: Math.round(l * 100) };
};

// Maneja el arrastre para la selección de color en el área de color
const startDrag = (event: MouseEvent):void => {
    const onMouseMove = (moveEvent: MouseEvent) => {
        if (colorArea.value) {
            const rect = colorArea.value.getBoundingClientRect();
            const x = Math.min(Math.max(0, moveEvent.clientX - rect.left), rect.width);
            const y = Math.min(Math.max(0, moveEvent.clientY - rect.top), rect.height);

            // Actualiza saturación (vertical) y luminosidad (horizontal) basados en la posición del ratón
            lightnessX.value = (x / rect.width) * 100;
            saturationY.value = 100 - (y / rect.height) * 100;
        }
    };

    const onMouseUp = ():void => {
        document.removeEventListener('mousemove', onMouseMove);
        document.removeEventListener('mouseup', onMouseUp);
    };

    document.addEventListener('mousemove', onMouseMove);
    document.addEventListener('mouseup', onMouseUp);
};

const updateSelectedColor = ():void => {
    selectedColor.value = colorHexWithAlpha.value;
    emits('onColorChange', 'colorButton', selectedColor.value);
};

</script>


<template>
    <div class="flex flex-wrap gap-0 w-full bg-white rounded-xl">


        <div class="w-full sm:w-[20%] md:w-[20%] card rounded-none flex items-center justify-center p-4">
            <div class="relative">
               <div class=" relative w-[140px] h-[80px]">
                   <span class="absolute top-0 left-0 w-[25px] h-[25px] border-t-[3px] border-l-[3px] rounded-tl-lg text-[#BCBCBC]"></span>
                   <span class="absolute top-0 right-0 w-[25px] h-[25px] border-t-[3px] border-r-[3px] rounded-tr-lg text-[#BCBCBC]"></span>
                   <span class="absolute bottom-0 left-0 w-[25px] h-[25px] border-b-[3px] border-l-[3px] rounded-bl-lg text-[#BCBCBC]"></span>
                   <span class="absolute bottom-0 right-0 w-[25px] h-[25px] border-b-[3px] border-r-[3px] rounded-br-lg text-[#BCBCBC]"></span>
               </div>
                <!-- Inputs en las esquinas con su estilo visual -->
                <input
                    class="input-corner top-left outline-none text-[#000000]"
                    type="text"
                    v-model="topLeft"
                    @input="onInputChange('topLeft', topLeft)"
                    :disabled="isSelected && !isFirstInputEditable"
                    placeholder="0"
                    maxlength="4"
                    inputmode="numeric" />

                <input
                    class="input-corner top-right outline-none text-[#000000]"
                    type="text"
                    v-model="topRight"
                    @input="onInputChange('topRight', topRight)"
                    :disabled="isSelected && !isFirstInputEditable"
                    placeholder="0"
                    maxlength="4"
                    inputmode="numeric" />

                <input
                    class="input-corner bottom-left outline-none  text-[#000000]"
                    type="text"
                    v-model="bottomLeft"
                    @input="onInputChange('bottomLeft', bottomLeft)"
                    :disabled="isSelected && !isFirstInputEditable"
                    placeholder="0"
                    maxlength="4"
                    inputmode="numeric" />

                <input
                    class="input-corner bottom-right outline-none  text-[#000000]"
                    type="text"
                    v-model="bottomRight"
                    @input="onInputChange('bottomRight', bottomRight)"
                    :disabled="isSelected && !isFirstInputEditable"
                    placeholder="0"
                    maxlength="4"
                    inputmode="numeric" />

                <!-- Ícono central con función de sincronización y selección -->
                <icon-lock-pop-up class="center-icon"  v-if="!isSelected"  @click="toggleSelection"/>
                <icon-unlock-pop-up class="center-icon"  v-if="isSelected"   @click="toggleSelection"/>
            </div>
        </div>

        <div class="w-full sm:w-[50%] md:w-[50%] card p-1 rounded-none">
            <div class="w-full flex flex-col justify-center items-center gap-[0.75rem] p-2">
                <div class="color-picker">
                    <!-- Área de selección de color -->
                    <div
                        class="color-area"
                        ref="colorArea"
                        @mousedown="startDrag"
                        :style="{ background: `linear-gradient(to right, transparent, hsl(${hue}, 100%, 50%)), linear-gradient(to top, black, transparent)` }"
                    >
                        <div
                            class="selector"
                            :style="{ top: `${saturationY}%`, left: `${lightnessX}%`, backgroundColor: colorHexWithAlpha }"
                        ></div>
                    </div>

                    <!-- Control de transparencia -->
                    <div class="alpha-slider-wrapper" >
                        <div class="alpha-background"></div>
                        <input
                            type="range"
                            min="0"
                            max="100"
                            step="1"
                            v-model="alpha"
                            class="alpha-slider"
                        />
                    </div>

                    <!-- Control de tono -->
                    <input
                        type="range"
                        min="0"
                        max="360"
                        step="1"
                        v-model="hue"
                        class="hue-slider"
                        @input="updateSelectedColor"
                    />
                </div>
            </div>
        </div>

        <div class="w-full sm:w-[30%] md:w-[30%] card  p-4 rounded-none">
            <div class="flex justify-between">
                <p class="text-[#6B6B6B] font-semibold">Seleccionar color</p>
                <p class="text-[#6B7280] cursor-pointer" @click="addColor">+ Agregar</p>
            </div>
            <div class="flex gap-2 flex-wrap mb-4 cursor-pointer overflow-y-scroll overflow-x-none h-[60px]">
                <div v-for="color in colors" :key="color" class="w-[26px] h-[26px] rounded-full cursor-pointer"
                     :class="{'border-3 border-gray-500': color === selectedColor}"
                     :style="{ backgroundColor: color }" @click="handleColorSelect(color)"></div>
            </div>
            <div class="flex justify-between">

                <div class="w-[30%] bg-white shadow-[0_2px_4px_rgba(0,0,0,0.5)] p-2 rounded-md flex items-center justify-center gap-4">
                    <p class="text-center text-sm text-[14px]">HEX</p>
                    <p class="text-center rotate-90 text-[14px]"><></p>
                </div>

                <div class="w-[30%] bg-white shadow-[0_2px_4px_rgba(0,0,0,0.5)] p-2 rounded-md flex items-center justify-center text-[14px]">
                    <p class="text-center">{{ hexadecimalSelect ? hexadecimalSelect : selectedColor }}</p>
                </div>

                <div class="w-[30%] bg-white shadow-[0_2px_4px_rgba(0,0,0,0.5)] p-2 rounded-md flex items-center justify-center">
                    <p class="text-center text-[14px]">
                        {{
                            (parseInt(
                                hexadecimalSelect ? hexadecimalSelect.slice(1, 3) : (selectedColor ? selectedColor.slice(1, 3) : "00"),
                                16
                            ) / 255 * 100).toFixed(0)
                        }} %
                    </p>
                </div>
            </div>
        </div>
    </div>

</template>

<style scoped>

/*SLIDER PICKER*/
.color-picker {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 8px;
    align-items: center;
}

.color-area {
    width: 100%;
    height: 150px;
    border-radius: 8px;
    cursor: crosshair;
    position: relative;
}

.selector {
    position: absolute;
    width: 12px;
    height: 12px;
    border: 2px solid white;
    border-radius: 50%;
    transform: translate(-50%, -50%);
}

.hue-slider,
.alpha-slider {
    width: 100%;
    appearance: none;
    height: 8px;
    border-radius: 4px;
    outline: none;
    cursor: pointer;
    background: linear-gradient(270deg,#e5e5e5,#e5e5e5d6, transparent);
}

.hue-slider {
    background: linear-gradient(to right, red, yellow, lime, cyan, blue, magenta, red);
}

.alpha-slider-wrapper {
    position: relative;
    width: 100%;
    display: flex;
    align-items: center;
    gap: 8px;
}

.alpha-background {
    background: repeating-conic-gradient(#ccc 0% 25%, #fff 0% 50%);
    background-size: 10px 10px;
    height: 8px;
    width: 100%;
    border-radius: 4px;
    position: absolute;
    top: 0;
    left: 0;
    z-index: 1;
}

.alpha-slider {
    width: 100%;
    z-index: 1;
}

.card {
    min-height: 130px;
}

/* Estilo para los inputs de las esquinas */
.input-corner {
    position: absolute;
    width: 25px;
    height: 25px;
    padding: 5px;
    font-size: 1rem;
    text-align: center;
    background-color: white;
    z-index: 1;

}

.top-left {
    top: -10px;
    left: -30px;
}

.top-right {
    top: -10px;
    right: -30px;
}

.bottom-left {
    bottom: -10px;
    left: -30px;
}

.bottom-right {
    bottom: -10px;
    right: -30px;
}

/* Estilo del ícono central */
.center-icon {
    font-size: 16px;
    cursor: pointer;
    position:absolute;
    top:50%;
    left:50%;
    transform:translate(-50%,-50%);
    z-index: 20;
}

</style>
