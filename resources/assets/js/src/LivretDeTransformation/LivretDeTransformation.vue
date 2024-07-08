<template>
    <div class="btn" @click="get_livret">Refresh</div>
    <div class="btn" @click="update_livret">Update</div>
    
    <p>
      <label for="showCheckBoxes">Checkboxes</label>
      <input id="showCheckBoxes" type="checkbox" v-model="config.checkboxes" />
  
      <label for="padding"> | Padding</label>
      <input id="padding" type="number" v-model.number="config.padding" />
  
      <label for="editable"> | Editable</label>
      <input id="editable" type="checkbox" v-model="config.editable" />
  
      <label for="disabled"> | Disabled</label>
      <input id="disabled" type="checkbox" v-model="config.disabled" />
  
      <label for="keyboardNavigation"> | Keyboard Navigation</label>
      <input
        id="keyboardNavigation"
        type="checkbox"
        v-model="config.keyboardNavigation"
      />
  
      <label for="DragandDrop"> | DragandDrop</label>
      <input id="DragandDrop" type="checkbox" v-model="config.dragAndDrop" />
  
      <label for="checkMode"> | Checkmode auto</label>
      <input
        id="checkMode"
        type="checkbox"
        :value="modeBool"
        @input="changeMode"
      />
    </p>
  
    <Treeview 
        :config="config" 
        :nodes="nodes"
        @nodeOpened="console.log(`node-opened`)"
        @nodeClosed="console.log('node-closed')"
        @nodeFocus="console.log('node-focus')"
        @nodeToggle="console.log('node-toggle')"
        @nodeBlur="console.log('node-blur')"
        @nodeEdit="console.log('node-edit')"
        @nodeChecked="console.log('node-checked')"
        @nodeUnchecked="console.log('node-unchecked')"
        @nodeDragstart="console.log('node-dragstart')"
        @nodeDragenter="console.log('node-dragenter')"
        @nodeDragleave="console.log('node-dragleave')"
        @nodeDragend="console.log('node-dragend')"
        @nodeOver="console.log('node-over')"
        @nodeDrop="console.log('node-drop')"> 
    </Treeview>
  </template>
  

<script setup>
  import Treeview from "vue3-treeview";
  import "vue3-treeview/dist/style.css";
  import { ref } from 'vue';
  
  const props = defineProps({
      wire: {},
      mingleData: {},
  });
  
  const {wire, mingleData} = props;

  const config = ref({
        roots: [],
        keyboardNavigation: true,
        dragAndDrop: false,
        checkboxes: true,
        editable: false,
        disabled: false,
        padding: 25,
        checkMode : 0 // 0: répercute l'activation des checkboxes vers les enfants. 1: ne répercute pas.
      });

  const nodes = ref( [] );
    
 function get_livret(){
    wire.get_livret()
    .then(data => {
        data = JSON.parse(data);
        nodes.value = data["nodes"];
        config.value["roots"] = data["roots"];
    }) 
}

function update_livret(){
  console.log(nodes.value)
  wire.update_livret(
    nodes.value
  )
  //get_livret()
}
  
</script>