import mingle from '@mingle/mingleVue'
import LivretDeTransformation from './LivretDeTransformation.vue'

// La chaine de caractere livret-de-transformation doit etre la meme que cette que getComponent du 
// composant Livewire qui va etre utilise. C'est tout ce qui compte. 
// Ca pourrait tout aussi bien etre toto ou zboobie.
mingle('livret-de-transformation.js', LivretDeTransformation)
