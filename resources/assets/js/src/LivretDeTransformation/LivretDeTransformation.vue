<template>
    <div class="btn" @click="get_livret">Refresh</div>
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
  
    <Treeview :config="config" :nodes="nodes['nodes']"> </Treeview>
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
        roots: ["id1", "id2"],
        keyboardNavigation: false,
        dragAndDrop: false,
        checkboxes: false,
        editable: false,
        disabled: false,
        padding: 25,
        checkMode : 1 // 0: répercute l'activation des checkboxes vers les enfants. 1: ne répercute pas.
      });

  const nodes = ref({ "nodes": {
        id1: {
          text: "text1",
          children: ["id11", "id12"],
        },
        id2: {
          text: "text2",
          children: [],
        },
        id11: {
          text: "text11",
          children: ["id111", "id112", "id113"],
        },
        id12: {
          text: "text12",
          children: [],
        },
        id111: {
          text: "id111",
        },
        id112: {
          text: "id112",
        },
        id113: {
          text: "id113",
        },
      }
    });
  
  const message = mingleData.message;
  
  console.log(message) // 'Message in a bottle 🍾'
  console.log(config.value["roots"]);
  console.log(nodes.value);
  
 function get_livret(){
    wire.get_livret()
    .then(data => {
        data = JSON.parse(data);
        nodes.value['nodes'] = data["nodes"];
        //console.log("nodes set");
        //console.log(config.value["roots"]);

        config.value["roots"]=data["roots"];
    }) 
}
  
</script>