<?php
/**
 * Course Content Management Modals
 * Contains all modal dialogs for chapter and lesson management
 */
?>

<!-- Add/Edit Chapter Modal -->
<div id="chapter-modal" class="hidden fixed z-10 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="chapter-form" method="post">
                <input type="hidden" name="action" value="save_chapter">
                <input type="hidden" name="chapter_id" id="chapter_id" value="">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-gray-900" id="chapter-modal-title">הוסף פרק חדש</h3>
                    </div>
                    
                    <div class="mb-4">
                        <label for="chapter_title" class="block text-sm font-medium text-gray-700 mb-1">שם הפרק <span class="text-red-500">*</span></label>
                        <input type="text" id="chapter_title" name="chapter_title" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="chapter_description" class="block text-sm font-medium text-gray-700 mb-1">תיאור הפרק (אופציונלי)</label>
                        <textarea id="chapter_description" name="chapter_description" rows="3" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        שמור
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm close-modal">
                        ביטול
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add/Edit Lesson Modal -->
<div id="lesson-modal" class="hidden fixed z-10 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="lesson-form" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save_lesson">
                <input type="hidden" name="lesson_id" id="lesson_id" value="">
                <input type="hidden" name="chapter_id" id="lesson_chapter_id" value="">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-gray-900" id="lesson-modal-title">הוסף שיעור חדש</h3>
                    </div>
                    
                    <div class="mb-4">
                        <label for="lesson_title" class="block text-sm font-medium text-gray-700 mb-1">שם השיעור <span class="text-red-500">*</span></label>
                        <input type="text" id="lesson_title" name="lesson_title" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="lesson_description" class="block text-sm font-medium text-gray-700 mb-1">תיאור השיעור (אופציונלי)</label>
                        <textarea id="lesson_description" name="lesson_description" rows="2" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label for="content_type" class="block text-sm font-medium text-gray-700 mb-1">סוג תוכן <span class="text-red-500">*</span></label>
                        <select id="content_type" name="content_type" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="video">וידאו</option>
                            <option value="pdf">PDF</option>
                            <option value="text">טקסט</option>
                        </select>
                    </div>
                    
                    <!-- Video/PDF Upload -->
                    <div id="file-upload-container" class="mb-4">
                        <label for="content_file" class="block text-sm font-medium text-gray-700 mb-1">העלאת קובץ <span class="text-red-500">*</span></label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="content_file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>העלה קובץ</span>
                                        <input id="content_file" name="content_file" type="file" class="sr-only">
                                    </label>
                                    <p class="pr-1">או גרור ושחרר לכאן</p>
                                </div>
                                <p class="text-xs text-gray-500" id="file-format-note">
                                    MP4 לקבצי וידאו, עד 500MB
                                </p>
                                <p class="text-xs text-gray-500 mt-1" id="selected-file-name"></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Text Content -->
                    <div id="text-content-container" class="mb-4 hidden">
                        <label for="content_text" class="block text-sm font-medium text-gray-700 mb-1">תוכן טקסט <span class="text-red-500">*</span></label>
                        <textarea id="content_text" name="content_text" rows="6" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                    
                    <!-- Video Duration (for video only) -->
                    <div id="duration-container" class="mb-4">
                        <label for="duration" class="block text-sm font-medium text-gray-700 mb-1">אורך בשניות (לוידאו בלבד)</label>
                        <input type="number" id="duration" name="duration" min="0" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">הזן את אורך הוידאו בשניות (לדוגמה: 300 עבור סרטון של 5 דקות)</p>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        שמור
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm close-modal">
                        ביטול
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="hidden fixed z-10 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="delete-form" method="post">
                <input type="hidden" name="action" id="delete_action" value="">
                <input type="hidden" name="chapter_id" id="delete_chapter_id" value="">
                <input type="hidden" name="lesson_id" id="delete_lesson_id" value="">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="ri-error-warning-line text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:mr-4 sm:text-right">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="delete-modal-title">
                                מחיקת פרק
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500" id="delete-modal-description">
                                    האם אתה בטוח שברצונך למחוק את הפרק "<span id="delete-item-name"></span>"? פעולה זו היא בלתי הפיכה.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        מחק
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm close-modal">
                        ביטול
                    </button>
                </div>
            </form>
        </div>
    </div>