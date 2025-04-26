<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { User, X, Eye, Edit2, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import { getAvatarProps } from '@/utils/avatar';
import { getDisplayRole } from '@/utils/roles';

const props = defineProps<{
  modelValue: Array<{
    id?: number;
    name: string;
    permission: string;
    role?: string;
  }>;
  activeUsers: Array<{
    id: number;
    name: string;
    role: string;
  }>;
  errors?: string[];
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: Array<{ id?: number; name: string; permission: string }>): void;
}>();

const addCollaborator = (userId: string) => {
  const user = props.activeUsers.find(u => u.id.toString() === userId);
  if (user && !props.modelValue.some(c => c.id === user.id)) {
    emit('update:modelValue', [
      ...props.modelValue,
      {
        id: user.id,
        name: user.name,
        role: user.role,
        permission: 'view'
      }
    ]);
  }
};

const updatePermission = (index: number, permission: string) => {
  const updatedCollaborators = [...props.modelValue];
  updatedCollaborators[index].permission = permission;
  emit('update:modelValue', updatedCollaborators);
};

const removeCollaborator = (index: number) => {
  const updatedCollaborators = [...props.modelValue];
  updatedCollaborators.splice(index, 1);
  emit('update:modelValue', updatedCollaborators);
};
</script>

<template>
  <div class="space-y-4">
    <div class="space-y-2">
      <Label>Add Team Members
        <span class="text-xs ml-1 bg-muted px-2 py-1 rounded-md text-muted-foreground">
          Optional
        </span>
      </Label>
      <Select @update:modelValue="addCollaborator">
        <SelectTrigger class="h-9">
          <SelectValue placeholder="Select team member" />
        </SelectTrigger>
        <SelectContent class="min-w-[200px]">
          <SelectItem 
            v-for="user in activeUsers" 
            :key="user.id" 
            :value="user.id.toString()"
          >
            {{ user.name }}
          </SelectItem>
        </SelectContent>
      </Select>
    </div>

    <div v-if="modelValue.length > 0" class="space-y-2">
      <div v-for="(collaborator, index) in modelValue" 
        :key="index"
        class="flex items-center gap-3 p-3 bg-muted rounded-lg hover:bg-muted/80 transition-colors"
      >
        <div class="flex items-center gap-3 flex-1">
          <Avatar class="h-8 w-8">
            <AvatarImage 
              v-if="getAvatarProps(collaborator).showAvatar" 
              :src="getAvatarProps(collaborator).src" 
              :alt="getAvatarProps(collaborator).alt" 
            />
            <AvatarFallback class="bg-primary/10">
              {{ getAvatarProps(collaborator).fallback }}
            </AvatarFallback>
          </Avatar>
          <div class="flex flex-col min-w-0">
            <span class="text-sm font-medium truncate">{{ collaborator.name }}</span>
            <span class="text-xs text-muted-foreground capitalize">{{ getDisplayRole(collaborator.role) }}</span>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <Select 
            :model-value="collaborator.permission"
            @update:modelValue="(value) => updatePermission(index, value)"
          >
            <SelectTrigger class="h-8 w-[100px]">
              <SelectValue>
                <div class="flex items-center gap-1.5">
                  <Eye v-if="collaborator.permission === 'view'" class="size-3.5" />
                  <Edit2 v-else class="size-3.5" />
                  <span class="capitalize text-xs">{{ collaborator.permission }}</span>
                </div>
              </SelectValue>
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="view">
                <div class="flex items-center gap-1.5">
                  <Eye class="size-3.5" />
                  <span>View</span>
                </div>
              </SelectItem>
              <SelectItem value="edit">
                <div class="flex items-center gap-1.5">
                  <Edit2 class="size-3.5" />
                  <span>Edit</span>
                </div>
              </SelectItem>
            </SelectContent>
          </Select>

          <Button
            variant="ghost"
            size="icon"
            class="h-8 w-8 rounded-full hover:bg-destructive/10 hover:text-destructive"
            @click="removeCollaborator(index)"
          >
            <X class="size-4" />
          </Button>
        </div>
      </div>
    </div>

    <div 
      v-if="!modelValue.length" 
      class="text-sm text-muted-foreground text-center p-6 bg-muted/50 rounded-lg"
    >
      <Users class="h-8 w-8 mx-auto mb-3 text-muted-foreground/50" />
      <p>No collaborators added yet</p>
    </div>

    <span v-if="errors?.length" class="text-sm text-red-500">
      {{ errors[0] }}
    </span>
  </div>
</template>