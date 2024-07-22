<template>    
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
        @nodeFocus="nodeFocus"
        @nodeToggle="console.log('node-toggle')"
        @nodeBlur="console.log('node-blur')"
        @nodeEdit="console.log('node-edit')"
        @nodeChecked="nodeChecked"
        @nodeUnchecked="nodeUnChecked"
        @nodeDragstart="console.log('node-dragstart')"
        @nodeDragenter="console.log('node-dragenter')"
        @nodeDragleave="console.log('node-dragleave')"
        @nodeDragend="console.log('node-dragend')"
        @nodeOver="console.log('node-over')"
        @nodeDrop="console.log('node-drop')"> 

        <template #before-input="props">
          <span> {{ props.node.state.checked }} </span>
        </template>
    </Treeview>
  </template>
  

<script setup>
  import Treeview from "vue3-treeview";
  import "./LivretDeTransformation.css";
  import { ref , onMounted} from 'vue';

  onMounted(() => {
    window.addEventListener('fcm-livretfcm--update', () => triggerUpdate())
    window.addEventListener('fcm-livretfcm--refresh', () => get_livret())
    get_livret();

})
  
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
        checkMode : 1 // 0: répercute l'activation des checkboxes vers les enfants. 1: ne répercute pas.
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

  function triggerUpdate(){
    wire.mountAction('updateAction', { livret : nodes.value })
  }

  function update_livret(){
    console.log(nodes.value)
    wire.update_livret(
      nodes.value
    )
    //get_livret()
  }

  function update_livret_thru_component_action(){
    wire.mountAction('updateAction', { livret : nodes.value })
  }

  function nodeChecked(node){
    if (node.state.checked){
      node.state={
                "checked": true,
                "opened" : true
      }
    }
    console.log('node-checked'); 
    //console.log(node.state.checked )
  }

  function nodeUnChecked(node){
    console.log('node-unchecked'); 
    //console.log(node.state.checked )
  }

  function nodeFocus(node){
    console.log('node-focus');
    //console.log(node )
  }
  
</script>