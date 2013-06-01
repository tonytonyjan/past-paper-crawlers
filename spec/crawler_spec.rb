# encoding: UTF-8
require 'spec_helper'
require 'json'

dirs = Dir['*'].select{|filename|
  filename != 'spec' && File.directory?(filename)
} - File.read('passed_list').split

dirs.each do |dir|
  describe dir do
    it 'should contains "past_papers.json".' do
      File.should exist("#{dir}/past_papers.json")
    end

    describe 'past_papers.json' do
      subject(:past_papers){JSON.parse(File.read("#{dir}/past_papers.json"))}

      it('should be an JSON Array'){should be_a_kind_of Array}

      its(:length){
        should eq past_papers.inject(0){|sum, item| sum + item["file_paths"].length}
      }

      (JSON.parse(File.read("#{dir}/past_papers.json")) rescue []).each_with_index do |past_paper, i|
        describe "item ##{i}" do
          subject{past_paper}
          
          it('should be a Hash'){should be_a_kind_of Hash}

          %w(school department subject exam_type).each{|key|
            its([key]){should be_a_kind_of String}
          }

          its(['year']){should be_a_kind_of Integer}
          its(['file_paths']){should be_a_kind_of Array}
          its(['exam_type']){should match /^(入學考|轉學考)$/}

          describe 'file_paths' do
            it('should not be empty'){should_not be_empty}
            (past_paper["file_paths"].is_a?(Array) ? past_paper["file_paths"] : []).each do |path|
              describe path do
                it('should be exist'){File.should exist(File.join(dir, path))}
              end
            end
          end
        end
      end
    end
  end  
end