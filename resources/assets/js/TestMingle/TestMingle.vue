<script setup>
const props = defineProps({
    wire: {},
    mingleData: {},
})

const {wire, mingleData} = props

const message = mingleData.message

console.log(message) // 'Message in a bottle 🍾'

wire.doubleIt(2)
    .then(data => {
        console.log(data) // 4
    })

</script>

<template>
    <div>
        Composant Vue charge dans Livewire par Mingle
    </div>
</template>
